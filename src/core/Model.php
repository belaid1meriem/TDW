<?php

namespace Core;

abstract class Model
{
    protected \PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
        
        if (empty($this->table)) {
            $className = (new \ReflectionClass($this))->getShortName();
            $tableName = strtolower(str_replace('Model', '', $className));
            $this->table = $tableName . 's';
        }
    }

    public function create(array $data): string|false
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Create error: " . $e->getMessage());
            return false;
        }
    }

    public function select(
        array $conditions = [], 
        array $columns = ['*'], 
        string $orderBy = '', 
        ?int $limit = null, 
        int $offset = 0
    ): array|false {
        $sql = "SELECT " . implode(", ", $columns) . " FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = array_map(fn($col) => "$col = :$col", array_keys($conditions));
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Select error: " . $e->getMessage());
            return false;
        }
    }

    public function find(mixed $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (\PDOException $e) {
            error_log("Find error: " . $e->getMessage());
            return false;
        }
    }

    public function findBy(array $conditions): array|false
    {
        $results = $this->select($conditions, ['*'], '', 1);
        return $results && !empty($results) ? $results[0] : false;
    }

    public function all(string $orderBy = ''): array|false
    {
        return $this->select([], ['*'], $orderBy);
    }

    public function update(array $data, array $conditions): int|false
    {
        if (empty($conditions)) {
            error_log("Update error: Conditions cannot be empty");
            return false;
        }
        
        $setClause = array_map(fn($col) => "$col = :set_$col", array_keys($data));
        $whereClause = array_map(fn($col) => "$col = :where_$col", array_keys($conditions));
        
        $sql = "UPDATE {$this->table} 
                SET " . implode(", ", $setClause) . " 
                WHERE " . implode(" AND ", $whereClause);
        
        $params = [];
        foreach ($data as $key => $value) {
            $params["set_$key"] = $value;
        }
        foreach ($conditions as $key => $value) {
            $params["where_$key"] = $value;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }

    public function updateById(mixed $id, array $data): int|false
    {
        return $this->update($data, [$this->primaryKey => $id]);
    }

    public function delete(array $conditions): int|false
    {
        if (empty($conditions)) {
            error_log("Delete error: Conditions cannot be empty");
            return false;
        }
        
        $whereClause = array_map(fn($col) => "$col = :$col", array_keys($conditions));
        $sql = "DELETE FROM {$this->table} WHERE " . implode(" AND ", $whereClause);
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteById(mixed $id): int|false
    {
        return $this->delete([$this->primaryKey => $id]);
    }

    public function count(array $conditions = []): int|false
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = array_map(fn($col) => "$col = :$col", array_keys($conditions));
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (\PDOException $e) {
            error_log("Count error: " . $e->getMessage());
            return false;
        }
    }

    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    public function query(string $sql, array $params = []): array|false
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return false;
        }
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollBack();
    }
}