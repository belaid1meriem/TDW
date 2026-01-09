<?php

namespace Core\AutoCrud;

use Core\Database;
use PDO;

/**
 * SchemaInspector - Introspects database schema to extract metadata
 * 
 * This class analyzes the database structure and returns normalized metadata
 * about tables, columns, foreign keys, enums, and relationships.
 */
class SchemaInspector
{
    private PDO $db;
    private string $database;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->database = $this->db->query('SELECT DATABASE()')->fetchColumn();
    }

    /**
     * Get all tables in the database (excluding system tables)
     */
    public function getTables(array $exclude = []): array
    {
        $sql = "SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_TYPE = 'BASE TABLE'
                ORDER BY TABLE_NAME";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['database' => $this->database]);
        
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Filter out excluded tables
        return array_diff($tables, $exclude);
    }

    /**
     * Get detailed column information for a table
     */
    public function getColumns(string $table): array
    {
        $sql = "SELECT 
                    COLUMN_NAME,
                    DATA_TYPE,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_KEY,
                    COLUMN_DEFAULT,
                    EXTRA,
                    CHARACTER_MAXIMUM_LENGTH,
                    NUMERIC_PRECISION,
                    COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table
                ORDER BY ORDINAL_POSITION";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table
        ]);

        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columnName = $row['COLUMN_NAME'];
            
            $columns[$columnName] = [
                'type' => $row['DATA_TYPE'],
                'full_type' => $row['COLUMN_TYPE'],
                'nullable' => $row['IS_NULLABLE'] === 'YES',
                'key' => $row['COLUMN_KEY'], // PRI, UNI, MUL
                'default' => $row['COLUMN_DEFAULT'],
                'auto_increment' => strpos($row['EXTRA'], 'auto_increment') !== false,
                'max_length' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'precision' => $row['NUMERIC_PRECISION'],
                'comment' => $row['COLUMN_COMMENT'],
                'enum_values' => $this->extractEnumValues($row['COLUMN_TYPE']),
                'is_primary' => $row['COLUMN_KEY'] === 'PRI',
                'is_foreign' => $row['COLUMN_KEY'] === 'MUL',
                'is_unique' => $row['COLUMN_KEY'] === 'UNI',
            ];
        }

        return $columns;
    }

    /**
     * Get foreign key relationships for a table
     */
    public function getForeignKeys(string $table): array
    {
        $sql = "SELECT 
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME,
                    CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table
                AND REFERENCED_TABLE_NAME IS NOT NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table
        ]);

        $foreignKeys = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $foreignKeys[$row['COLUMN_NAME']] = [
                'table' => $row['REFERENCED_TABLE_NAME'],
                'column' => $row['REFERENCED_COLUMN_NAME'],
                'constraint' => $row['CONSTRAINT_NAME']
            ];
        }

        return $foreignKeys;
    }

    /**
     * Detect pivot tables (junction tables for many-to-many relationships)
     * 
     * A table is considered a pivot table if:
     * - It has exactly 2 foreign keys
     * - Both foreign keys are part of the primary key
     * - It has no other significant columns
     */
    public function detectPivotTables(): array
    {
        $tables = $this->getTables();
        $pivotTables = [];

        foreach ($tables as $table) {
            $columns = $this->getColumns($table);
            $foreignKeys = $this->getForeignKeys($table);

            // Must have exactly 2 foreign keys
            if (count($foreignKeys) !== 2) {
                continue;
            }

            // Count primary key columns
            $primaryKeyCount = 0;
            foreach ($columns as $col => $meta) {
                if ($meta['is_primary']) {
                    $primaryKeyCount++;
                }
            }

            // Both FKs should be part of primary key (composite PK)
            if ($primaryKeyCount !== 2) {
                continue;
            }

            // Extract the two foreign key columns
            $fkColumns = array_keys($foreignKeys);
            
            // Determine which tables are being related
            $relatedTables = [
                $foreignKeys[$fkColumns[0]]['table'],
                $foreignKeys[$fkColumns[1]]['table']
            ];

            $pivotTables[$table] = [
                'table1' => $relatedTables[0],
                'table2' => $relatedTables[1],
                'fk1' => $fkColumns[0],
                'fk2' => $fkColumns[1]
            ];
        }

        return $pivotTables;
    }

    /**
     * Get the primary key column(s) for a table
     */
    public function getPrimaryKey(string $table): string|array
    {
        $columns = $this->getColumns($table);
        $primaryKeys = [];

        foreach ($columns as $col => $meta) {
            if ($meta['is_primary']) {
                $primaryKeys[] = $col;
            }
        }

        // Return single string if only one PK, array if composite
        return count($primaryKeys) === 1 ? $primaryKeys[0] : $primaryKeys;
    }

    /**
     * Get indices for a table
     */
    public function getIndices(string $table): array
    {
        $sql = "SELECT 
                    INDEX_NAME,
                    COLUMN_NAME,
                    NON_UNIQUE,
                    SEQ_IN_INDEX
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table
                ORDER BY INDEX_NAME, SEQ_IN_INDEX";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table
        ]);

        $indices = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $indexName = $row['INDEX_NAME'];
            
            if (!isset($indices[$indexName])) {
                $indices[$indexName] = [
                    'columns' => [],
                    'unique' => $row['NON_UNIQUE'] == 0
                ];
            }
            
            $indices[$indexName]['columns'][] = $row['COLUMN_NAME'];
        }

        return $indices;
    }

    /**
     * Extract enum values from COLUMN_TYPE
     * e.g., "enum('admin','user','guest')" => ['admin', 'user', 'guest']
     */
    private function extractEnumValues(string $columnType): ?array
    {
        if (!str_starts_with($columnType, 'enum(')) {
            return null;
        }

        $enumString = substr($columnType, 5, -1); // Remove "enum(" and ")"
        $values = [];

        // Parse the quoted values
        preg_match_all("/'([^']*)'/", $enumString, $matches);
        
        return $matches[1] ?? [];
    }

    /**
     * Get complete schema metadata for a table
     */
    public function getTableMetadata(string $table): array
    {
        return [
            'name' => $table,
            'columns' => $this->getColumns($table),
            'foreign_keys' => $this->getForeignKeys($table),
            'primary_key' => $this->getPrimaryKey($table),
            'indices' => $this->getIndices($table)
        ];
    }

    /**
     * Guess the display column for a table (for dropdown labels)
     * Looks for common naming patterns: name, title, label, description
     */
    public function guessDisplayColumn(string $table): string
    {
        $columns = $this->getColumns($table);
        
        // Priority order for display column
        $candidates = ['name', 'title', 'label', 'nom', 'libelle', 'designation'];
        
        foreach ($candidates as $candidate) {
            if (isset($columns[$candidate])) {
                return $candidate;
            }
        }

        // Fallback: first non-PK varchar column
        foreach ($columns as $col => $meta) {
            if (!$meta['is_primary'] && in_array($meta['type'], ['varchar', 'char', 'text'])) {
                return $col;
            }
        }

        // Last resort: primary key
        $pk = $this->getPrimaryKey($table);
        return is_array($pk) ? $pk[0] : $pk;
    }

    /**
     * Check if a table exists
     */
    public function tableExists(string $table): bool
    {
        $sql = "SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get table statistics (row count, size, etc.)
     */
    public function getTableStats(string $table): array
    {
        $sql = "SELECT 
                    TABLE_ROWS as row_count,
                    DATA_LENGTH as data_size,
                    INDEX_LENGTH as index_size,
                    AUTO_INCREMENT as next_id,
                    CREATE_TIME as created_at,
                    UPDATE_TIME as updated_at
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}