<?php

namespace Core\AutoCrud;

use Core\Database;
use PDO;

/**
 * CrudEngine - Generic CRUD operations for any table
 * 
 * Handles listing, creating, updating, and deleting records
 * with automatic JOIN resolution, filtering, and pagination
 */
class CrudEngine
{
    private PDO $db;
    private VirtualModel $model;
    private ForeignKeyResolver $fkResolver;

    public function __construct(VirtualModel $model)
    {
        $this->db = Database::getConnection();
        $this->model = $model;
        $this->fkResolver = ForeignKeyResolver::getInstance();
    }

    /**
     * List records with optional filters, pagination, and sorting
     * 
     * @param array $filters Filter conditions ['column' => 'value']
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page
     * @param string $orderBy Sort expression (e.g., 'id DESC')
     * @return array ['data' => [...], 'total' => 100, 'page' => 1, 'perPage' => 20]
     */
    public function list(
        array $filters = [], 
        int $page = 1, 
        int $perPage = 20, 
        string $orderBy = ''
    ): array {
        $table = $this->model->table;
        
        // Build SELECT clause with JOINs for foreign keys
        $selectClause = $this->buildSelectClause();
        
        // Build FROM clause with JOINs
        $fromClause = $this->buildFromClause();
        
        // Build WHERE clause
        list($whereClause, $params) = $this->buildWhereClause($filters);
        
        // Build ORDER BY clause
        $orderClause = $this->buildOrderClause($orderBy);
        
        // Build LIMIT clause
        $offset = ($page - 1) * $perPage;
        $limitClause = "LIMIT {$perPage} OFFSET {$offset}";
        
        // Construct final query
        $sql = "SELECT {$selectClause} 
                FROM {$fromClause} 
                {$whereClause} 
                {$orderClause} 
                {$limitClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $total = $this->count($filters);
            
            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'pages' => ceil($total / $perPage)
            ];
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::list error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            return [
                'data' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'pages' => 0
            ];
        }
    }

    /**
     * Get a single record by ID
     */
    public function show(mixed $id): ?array
    {
        $table = $this->model->table;
        $pk = is_array($this->model->primaryKey) 
            ? $this->model->primaryKey[0] 
            : $this->model->primaryKey;
        
        $sql = "SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            return $record ?: null;
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::show error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new record
     */
    public function create(array $data): int|false
    {
        $table = $this->model->table;
        
        // Filter out non-editable columns
        $data = $this->filterEditableData($data);
        
        // Handle timestamps
        if ($this->model->hasTimestamps()) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }
        
        // Build INSERT query
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = "INSERT INTO `{$table}` 
                (`" . implode('`, `', $columns) . "`) 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            return (int) $this->db->lastInsertId();
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::create error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            return false;
        }
    }

    /**
     * Update an existing record
     */
    public function update(mixed $id, array $data): bool
    {
        $table = $this->model->table;
        $pk = is_array($this->model->primaryKey) 
            ? $this->model->primaryKey[0] 
            : $this->model->primaryKey;
        
        // Filter out non-editable columns
        $data = $this->filterEditableData($data);
        
        // Handle timestamps
        if ($this->model->hasTimestamps()) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if (empty($data)) {
            return false;
        }
        
        // Build UPDATE query
        $setClause = [];
        foreach (array_keys($data) as $col) {
            $setClause[] = "`{$col}` = :{$col}";
        }
        
        $sql = "UPDATE `{$table}` 
                SET " . implode(', ', $setClause) . " 
                WHERE `{$pk}` = :_id";
        
        $data['_id'] = $id;
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::update error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            return false;
        }
    }

    /**
     * Delete a record (soft delete if supported)
     */
    public function delete(mixed $id): bool
    {
        $table = $this->model->table;
        $pk = is_array($this->model->primaryKey) 
            ? $this->model->primaryKey[0] 
            : $this->model->primaryKey;
        
        // Use soft delete if available
        if ($this->model->hasSoftDeletes()) {
            $sql = "UPDATE `{$table}` 
                    SET `deleted_at` = :now 
                    WHERE `{$pk}` = :id";
            
            try {
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    'id' => $id,
                    'now' => date('Y-m-d H:i:s')
                ]);
            } catch (\PDOException $e) {
                error_log("CrudEngine::delete (soft) error: " . $e->getMessage());
                return false;
            }
        }
        
        // Hard delete
        $sql = "DELETE FROM `{$table}` WHERE `{$pk}` = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count records with optional filters
     */
    public function count(array $filters = []): int
    {
        $table = $this->model->table;
        
        list($whereClause, $params) = $this->buildWhereClause($filters);
        
        $sql = "SELECT COUNT(*) FROM `{$table}` {$whereClause}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return (int) $stmt->fetchColumn();
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Build SELECT clause with foreign key JOINs
     */
    private function buildSelectClause(): string
    {
        $table = $this->model->table;
        $selects = ["`{$table}`.*"];
        
        // Add foreign key label columns
        foreach ($this->model->listJoins as $column => $joinInfo) {
            $foreignTable = $joinInfo['foreign_table'];
            $displayCol = $joinInfo['display_column'];
            $alias = $joinInfo['alias'];
            
            $selects[] = "`{$foreignTable}`.`{$displayCol}` AS `{$alias}`";
        }
        
        return implode(', ', $selects);
    }

    /**
     * Build FROM clause with LEFT JOINs
     */
    private function buildFromClause(): string
    {
        $table = $this->model->table;
        $from = "`{$table}`";
        
        // Add LEFT JOINs for foreign keys
        foreach ($this->model->listJoins as $column => $joinInfo) {
            $foreignTable = $joinInfo['foreign_table'];
            $foreignKey = $joinInfo['foreign_key'];
            
            $from .= " LEFT JOIN `{$foreignTable}` 
                      ON `{$table}`.`{$column}` = `{$foreignTable}`.`{$foreignKey}`";
        }
        
        return $from;
    }

    /**
     * Build WHERE clause from filters
     */
    private function buildWhereClause(array $filters): array
    {
        $table = $this->model->table;
        $conditions = [];
        $params = [];
        
        // Add soft delete filter
        if ($this->model->hasSoftDeletes()) {
            $conditions[] = "`{$table}`.`deleted_at` IS NULL";
        }
        
        foreach ($filters as $column => $value) {
            // Skip empty filters
            if ($value === '' || $value === null) {
                continue;
            }
            
            // Skip non-existent columns
            if (!isset($this->model->columns[$column])) {
                continue;
            }
            
            $filterConfig = $this->model->filters[$column] ?? null;
            
            if ($filterConfig) {
                switch ($filterConfig['type']) {
                    case 'text':
                        $conditions[] = "`{$table}`.`{$column}` LIKE :filter_{$column}";
                        $params["filter_{$column}"] = "%{$value}%";
                        break;
                        
                    case 'relation':
                    case 'enum':
                    case 'boolean':
                        $conditions[] = "`{$table}`.`{$column}` = :filter_{$column}";
                        $params["filter_{$column}"] = $value;
                        break;
                        
                    case 'date_range':
                        // Expecting value like "2024-01-01,2024-12-31"
                        if (strpos($value, ',') !== false) {
                            list($start, $end) = explode(',', $value, 2);
                            $conditions[] = "`{$table}`.`{$column}` BETWEEN :filter_{$column}_start AND :filter_{$column}_end";
                            $params["filter_{$column}_start"] = $start;
                            $params["filter_{$column}_end"] = $end;
                        }
                        break;
                }
            } else {
                // Default: exact match
                $conditions[] = "`{$table}`.`{$column}` = :filter_{$column}";
                $params["filter_{$column}"] = $value;
            }
        }
        
        $whereClause = !empty($conditions) 
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';
        
        return [$whereClause, $params];
    }

    /**
     * Build ORDER BY clause
     */
    private function buildOrderClause(string $orderBy): string
    {
        if (empty($orderBy)) {
            $pk = is_array($this->model->primaryKey) 
                ? $this->model->primaryKey[0] 
                : $this->model->primaryKey;
            $orderBy = "{$pk} DESC";
        }
        
        return "ORDER BY {$orderBy}";
    }

    /**
     * Filter data to only include editable columns
     */
    private function filterEditableData(array $data): array
    {
        $filtered = [];
        
        foreach ($data as $column => $value) {
            // Skip if column doesn't exist
            if (!isset($this->model->columns[$column])) {
                continue;
            }
            
            // Skip if not editable
            if (!$this->model->isEditable($column)) {
                continue;
            }
            
            $filtered[$column] = $value;
        }
        
        return $filtered;
    }

    /**
     * Search records by keyword across searchable columns
     */
    public function search(string $keyword, int $limit = 10): array
    {
        if (empty($this->model->searchableColumns)) {
            return [];
        }
        
        $table = $this->model->table;
        $conditions = [];
        $params = [];
        
        foreach ($this->model->searchableColumns as $column) {
            $conditions[] = "`{$column}` LIKE :keyword";
        }
        
        $whereClause = 'WHERE ' . implode(' OR ', $conditions);
        $params['keyword'] = "%{$keyword}%";
        
        // Add soft delete filter
        if ($this->model->hasSoftDeletes()) {
            $whereClause .= " AND `deleted_at` IS NULL";
        }
        
        $sql = "SELECT * FROM `{$table}` {$whereClause} LIMIT {$limit}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("CrudEngine::search error: " . $e->getMessage());
            return [];
        }
    }
}