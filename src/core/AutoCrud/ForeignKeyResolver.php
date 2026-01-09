<?php

namespace Core\AutoCrud;

use Core\Database;
use PDO;

/**
 * ForeignKeyResolver - Handles foreign key display and selection
 * 
 * Provides options for <select> dropdowns and resolves IDs to display labels
 */
class ForeignKeyResolver
{
    private PDO $db;
    private SchemaInspector $inspector;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->inspector = new SchemaInspector();
    }

    /**
     * Get options for a foreign key select dropdown
     * 
     * @param array $relation Foreign key metadata ['table' => 'users', 'column' => 'id']
     * @param string|null $displayColumn Override display column
     * @return array ['id' => 'display_label']
     */
    public function getOptions(array $relation, ?string $displayColumn = null): array
    {
        $foreignTable = $relation['table'];
        $foreignKey = $relation['column'];
        
        // Use cache to avoid repeated queries
        $cacheKey = "{$foreignTable}:{$foreignKey}:{$displayColumn}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Determine display column
        if ($displayColumn === null) {
            $displayColumn = $this->inspector->guessDisplayColumn($foreignTable);
        }

        // Build query
        $sql = "SELECT `{$foreignKey}` as `value`, `{$displayColumn}` as `label` 
                FROM `{$foreignTable}` 
                ORDER BY `{$displayColumn}`";

        try {
            $stmt = $this->db->query($sql);
            $options = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[$row['value']] = $row['label'];
            }

            self::$cache[$cacheKey] = $options;
            return $options;
            
        } catch (\PDOException $e) {
            error_log("ForeignKeyResolver error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resolve a foreign key value to its display label
     * 
     * @param string $table The foreign table name
     * @param mixed $id The foreign key value
     * @param string|null $displayColumn Override display column
     * @return string|null The display label or null if not found
     */
    public function resolve(string $table, mixed $id, ?string $displayColumn = null): ?string
    {
        if ($id === null) {
            return null;
        }

        // Determine display column
        if ($displayColumn === null) {
            $displayColumn = $this->inspector->guessDisplayColumn($table);
        }

        // Get primary key of foreign table
        $primaryKey = $this->inspector->getPrimaryKey($table);
        if (is_array($primaryKey)) {
            $primaryKey = $primaryKey[0]; // Use first column of composite key
        }

        // Build query
        $sql = "SELECT `{$displayColumn}` 
                FROM `{$table}` 
                WHERE `{$primaryKey}` = :id 
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $result = $stmt->fetchColumn();
            return $result ?: null;
            
        } catch (\PDOException $e) {
            error_log("ForeignKeyResolver::resolve error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve multiple foreign key values at once (batch operation)
     * 
     * @param string $table The foreign table name
     * @param array $ids Array of foreign key values
     * @param string|null $displayColumn Override display column
     * @return array ['id' => 'label']
     */
    public function resolveMany(string $table, array $ids, ?string $displayColumn = null): array
    {
        if (empty($ids)) {
            return [];
        }

        // Remove nulls and duplicates
        $ids = array_filter(array_unique($ids), fn($id) => $id !== null);
        
        if (empty($ids)) {
            return [];
        }

        // Determine display column
        if ($displayColumn === null) {
            $displayColumn = $this->inspector->guessDisplayColumn($table);
        }

        // Get primary key
        $primaryKey = $this->inspector->getPrimaryKey($table);
        if (is_array($primaryKey)) {
            $primaryKey = $primaryKey[0];
        }

        // Build query with IN clause
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT `{$primaryKey}` as `id`, `{$displayColumn}` as `label` 
                FROM `{$table}` 
                WHERE `{$primaryKey}` IN ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($ids));
            
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$row['id']] = $row['label'];
            }

            return $results;
            
        } catch (\PDOException $e) {
            error_log("ForeignKeyResolver::resolveMany error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get options with additional filtering
     * Useful for dependent dropdowns
     * 
     * @param array $relation Foreign key metadata
     * @param array $conditions WHERE conditions ['column' => 'value']
     * @param string|null $displayColumn Override display column
     * @return array
     */
    public function getFilteredOptions(
        array $relation, 
        array $conditions = [], 
        ?string $displayColumn = null
    ): array {
        $foreignTable = $relation['table'];
        $foreignKey = $relation['column'];
        
        if ($displayColumn === null) {
            $displayColumn = $this->inspector->guessDisplayColumn($foreignTable);
        }

        // Build WHERE clause
        $whereClauses = [];
        $params = [];
        foreach ($conditions as $col => $value) {
            $whereClauses[] = "`{$col}` = ?";
            $params[] = $value;
        }
        
        $whereSQL = !empty($whereClauses) 
            ? 'WHERE ' . implode(' AND ', $whereClauses)
            : '';

        // Build query
        $sql = "SELECT `{$foreignKey}` as `value`, `{$displayColumn}` as `label` 
                FROM `{$foreignTable}` 
                {$whereSQL}
                ORDER BY `{$displayColumn}`";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $options = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[$row['value']] = $row['label'];
            }

            return $options;
            
        } catch (\PDOException $e) {
            error_log("ForeignKeyResolver::getFilteredOptions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a foreign key value exists
     */
    public function exists(string $table, mixed $id): bool
    {
        $primaryKey = $this->inspector->getPrimaryKey($table);
        if (is_array($primaryKey)) {
            $primaryKey = $primaryKey[0];
        }

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$primaryKey}` = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Clear the options cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Get a single instance (singleton pattern)
     */
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}