<?php

namespace Core\AutoCrud;

/**
 * VirtualModel - Rich metadata object representing a database table
 * 
 * This class transforms raw schema information into a structured model
 * with computed properties for CRUD operations, relations, and UI rendering.
 */
class VirtualModel
{
    public string $table;
    public string|array $primaryKey;
    public array $columns = [];
    public array $relations = [];        // FK relationships
    public array $pivotTables = [];      // Many-to-many via pivot
    public array $listJoins = [];        // Computed: JOINs for list view
    public array $filters = [];          // Computed: available filters
    public array $validationRules = [];  // Computed: validation from schema
    public string $displayColumn;        // Column to use for labels
    public array $hiddenColumns = [];    // Columns to hide in forms
    public array $readonlyColumns = [];  // Columns that can't be edited
    public array $searchableColumns = [];// Columns for search
    
    private SchemaInspector $inspector;
    private array $config = [];          // Per-table config overrides

    private function __construct(string $table, array $config = [])
    {
        $this->table = $table;
        $this->config = $config;
        $this->inspector = new SchemaInspector();
        
        $this->build();
    }

    /**
     * Factory method: Create VirtualModel from table name
     */
    public static function fromTable(string $table, array $config = []): self
    {
        return new self($table, $config);
    }

    /**
     * Build the complete model metadata
     */
    private function build(): void
    {
        // Get schema metadata
        $metadata = $this->inspector->getTableMetadata($this->table);
        
        $this->columns = $metadata['columns'];
        $this->primaryKey = $metadata['primary_key'];
        $this->relations = $metadata['foreign_keys'];
        
        // Apply config overrides
        $this->applyConfig();
        
        // Compute derived properties
        $this->computeListJoins();
        $this->computeFilters();
        $this->computeValidationRules();
        $this->computeSearchableColumns();
        $this->computeHiddenColumns();
        $this->computeReadonlyColumns();
        
        // Set display column
        $this->displayColumn = $this->config['display_column'] 
            ?? $this->inspector->guessDisplayColumn($this->table);
    }

    /**
     * Apply configuration overrides from app/config/tables.php
     */
    private function applyConfig(): void
    {
        if (isset($this->config['hidden_columns'])) {
            $this->hiddenColumns = array_merge(
                $this->hiddenColumns, 
                $this->config['hidden_columns']
            );
        }

        if (isset($this->config['readonly_columns'])) {
            $this->readonlyColumns = array_merge(
                $this->readonlyColumns, 
                $this->config['readonly_columns']
            );
        }

        if (isset($this->config['relations'])) {
            $this->relations = array_merge(
                $this->relations, 
                $this->config['relations']
            );
        }
    }

    /**
     * Compute which JOINs to add for list views
     * For each FK, we'll LEFT JOIN the foreign table to show labels
     */
    private function computeListJoins(): void
    {
        foreach ($this->relations as $column => $relation) {
            $foreignTable = $relation['table'];
            $foreignColumn = $relation['column'];
            
            // Get the display column of the foreign table
            $foreignDisplayCol = $this->config['relations'][$column]['display'] 
                ?? $this->inspector->guessDisplayColumn($foreignTable);
            
            $this->listJoins[$column] = [
                'foreign_table' => $foreignTable,
                'foreign_key' => $foreignColumn,
                'display_column' => $foreignDisplayCol,
                'alias' => $column . '__label'
            ];
        }
    }

    /**
     * Compute available filters based on column types
     */
    private function computeFilters(): void
    {
        foreach ($this->columns as $col => $meta) {
            // Skip hidden columns
            if (in_array($col, $this->hiddenColumns)) {
                continue;
            }

            // Primary keys and auto-increment columns aren't filterable
            if ($meta['is_primary'] || $meta['auto_increment']) {
                continue;
            }

            // Foreign keys → select filter
            if (isset($this->relations[$col])) {
                $this->filters[$col] = [
                    'type' => 'relation',
                    'relation' => $this->relations[$col]
                ];
                continue;
            }

            // Enums → select filter
            if (!empty($meta['enum_values'])) {
                $this->filters[$col] = [
                    'type' => 'enum',
                    'values' => $meta['enum_values']
                ];
                continue;
            }

            // Status/boolean columns → select filter
            if (in_array($col, ['status', 'active', 'enabled', 'published']) || 
                $meta['type'] === 'tinyint') {
                $this->filters[$col] = [
                    'type' => 'boolean'
                ];
                continue;
            }

            // Date columns → date range filter
            if (in_array($meta['type'], ['date', 'datetime', 'timestamp'])) {
                $this->filters[$col] = [
                    'type' => 'date_range'
                ];
                continue;
            }

            // Text columns → search filter
            if (in_array($meta['type'], ['varchar', 'char', 'text'])) {
                $this->filters[$col] = [
                    'type' => 'text'
                ];
            }
        }
    }

    /**
     * Generate validation rules from schema
     */
    private function computeValidationRules(): void
    {
        foreach ($this->columns as $col => $meta) {
            $rules = [];

            // Skip auto-increment and primary keys on create
            if ($meta['auto_increment']) {
                continue;
            }

            // Required if not nullable and no default
            if (!$meta['nullable'] && $meta['default'] === null) {
                $rules[] = 'required';
            }

            // Type-specific rules
            switch ($meta['type']) {
                case 'varchar':
                case 'char':
                    if ($meta['max_length']) {
                        $rules[] = "max:{$meta['max_length']}";
                    }
                    break;

                case 'int':
                case 'bigint':
                case 'smallint':
                case 'tinyint':
                    $rules[] = 'integer';
                    break;

                case 'decimal':
                case 'float':
                case 'double':
                    $rules[] = 'numeric';
                    break;

                case 'date':
                    $rules[] = 'date';
                    break;

                case 'email':
                    $rules[] = 'email';
                    break;

                case 'enum':
                    if (!empty($meta['enum_values'])) {
                        $rules[] = 'in:' . implode(',', $meta['enum_values']);
                    }
                    break;
            }

            // Foreign key validation
            if (isset($this->relations[$col])) {
                $relation = $this->relations[$col];
                $rules[] = "exists:{$relation['table']},{$relation['column']}";
            }

            // Unique constraint
            if ($meta['is_unique']) {
                $rules[] = "unique:{$this->table},{$col}";
            }

            if (!empty($rules)) {
                $this->validationRules[$col] = implode('|', $rules);
            }
        }
    }

    /**
     * Determine which columns are searchable
     */
    private function computeSearchableColumns(): void
    {
        foreach ($this->columns as $col => $meta) {
            // Text columns are searchable
            if (in_array($meta['type'], ['varchar', 'char', 'text', 'mediumtext', 'longtext'])) {
                if (!in_array($col, ['password', 'remember_token'])) {
                    $this->searchableColumns[] = $col;
                }
            }
        }
    }

    /**
     * Auto-hide sensitive columns
     */
    private function computeHiddenColumns(): void
    {
        $sensitivePatterns = [
            'password', 
            'token', 
            'secret', 
            'salt', 
            'hash',
            'remember_token',
            'api_token'
        ];

        foreach ($this->columns as $col => $meta) {
            foreach ($sensitivePatterns as $pattern) {
                if (stripos($col, $pattern) !== false) {
                    $this->hiddenColumns[] = $col;
                    break;
                }
            }
        }

        $this->hiddenColumns = array_unique($this->hiddenColumns);
    }

    /**
     * Auto-detect readonly columns
     */
    private function computeReadonlyColumns(): void
    {
        $readonlyPatterns = ['created_at', 'updated_at', 'deleted_at'];

        foreach ($this->columns as $col => $meta) {
            if (in_array($col, $readonlyPatterns) || $meta['auto_increment']) {
                $this->readonlyColumns[] = $col;
            }
        }

        $this->readonlyColumns = array_unique($this->readonlyColumns);
    }

    /**
     * Check if a column should be shown in list view
     */
    public function isListable(string $column): bool
    {
        if (in_array($column, $this->hiddenColumns)) {
            return false;
        }

        // Hide large text fields in list view
        $meta = $this->columns[$column] ?? null;
        if ($meta && in_array($meta['type'], ['text', 'mediumtext', 'longtext', 'blob'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if a column should be editable in forms
     */
    public function isEditable(string $column): bool
    {
        if (in_array($column, $this->hiddenColumns)) {
            return false;
        }

        if (in_array($column, $this->readonlyColumns)) {
            return false;
        }

        $meta = $this->columns[$column] ?? null;
        if ($meta && $meta['auto_increment']) {
            return false;
        }

        return true;
    }

    /**
     * Get human-readable label for a column
     */
    public function getLabel(string $column): string
    {
        // Check if custom label exists in config
        if (isset($this->config['labels'][$column])) {
            return $this->config['labels'][$column];
        }

        // Use column comment if available
        $meta = $this->columns[$column] ?? null;
        if ($meta && !empty($meta['comment'])) {
            return $meta['comment'];
        }

        // Convert snake_case to Title Case
        return ucwords(str_replace('_', ' ', $column));
    }

    /**
     * Get display name for the table
     */
    public function getTableLabel(): string
    {
        return $this->config['label'] ?? ucwords(str_replace('_', ' ', $this->table));
    }

    /**
     * Check if table has soft deletes
     */
    public function hasSoftDeletes(): bool
    {
        return isset($this->columns['deleted_at']);
    }

    /**
     * Check if table has timestamps
     */
    public function hasTimestamps(): bool
    {
        return isset($this->columns['created_at']) && isset($this->columns['updated_at']);
    }

    /**
     * Get filterable columns
     */
    public function getFilterableColumns(): array
    {
        return array_keys($this->filters);
    }

    /**
     * Export model as array (for debugging/serialization)
     */
    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'primary_key' => $this->primaryKey,
            'columns' => $this->columns,
            'relations' => $this->relations,
            'filters' => $this->filters,
            'display_column' => $this->displayColumn,
            'hidden_columns' => $this->hiddenColumns,
            'readonly_columns' => $this->readonlyColumns,
            'searchable_columns' => $this->searchableColumns,
        ];
    }
}