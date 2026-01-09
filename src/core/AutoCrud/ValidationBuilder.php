<?php

namespace Core\AutoCrud;

/**
 * ValidationBuilder - Generates validation rules from VirtualModel
 * 
 * Automatically creates Laravel-style validation rules based on
 * database schema metadata
 */
class ValidationBuilder
{
    /**
     * Build validation rules from VirtualModel
     * 
     * @param VirtualModel $model The model to generate rules for
     * @param mixed|null $id Record ID for update operations (to exclude from unique checks)
     * @return array Validation rules ['column' => 'rule1|rule2']
     */
    public static function buildFromModel(VirtualModel $model, mixed $id = null): array
    {
        $rules = [];
        
        foreach ($model->columns as $column => $meta) {
            // Skip auto-increment and non-editable columns
            if (!$model->isEditable($column)) {
                continue;
            }
            
            $columnRules = self::buildColumnRules($column, $meta, $model, $id);
            
            if (!empty($columnRules)) {
                $rules[$column] = implode('|', $columnRules);
            }
        }
        
        return $rules;
    }

    /**
     * Build validation rules for a single column
     */
    private static function buildColumnRules(
        string $column, 
        array $meta, 
        VirtualModel $model, 
        mixed $id = null
    ): array {
        $rules = [];
        
        // Required rule
        if (!$meta['nullable'] && $meta['default'] === null) {
            $rules[] = 'required';
        }
        
        // Type-specific rules
        $rules = array_merge($rules, self::getTypeRules($meta));
        
        // Length rules
        if ($meta['max_length']) {
            $rules[] = "max:{$meta['max_length']}";
        }
        
        // Enum rules
        if (!empty($meta['enum_values'])) {
            $rules[] = 'in:' . implode(',', $meta['enum_values']);
        }
        
        // Foreign key validation
        if (isset($model->relations[$column])) {
            $relation = $model->relations[$column];
            $rules[] = "exists:{$relation['table']},{$relation['column']}";
        }
        
        // Unique validation
        if ($meta['is_unique']) {
            if ($id !== null) {
                // For updates: exclude current record from unique check
                $rules[] = "unique:{$model->table},{$column},{$id}";
            } else {
                $rules[] = "unique:{$model->table},{$column}";
            }
        }
        
        return $rules;
    }

    /**
     * Get validation rules based on data type
     */
    private static function getTypeRules(array $meta): array
    {
        $rules = [];
        
        switch ($meta['type']) {
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
                
            case 'datetime':
            case 'timestamp':
                // Could add datetime validation if Validator supports it
                break;
                
            case 'varchar':
            case 'char':
                // Check for email pattern in column name
                if (stripos($meta['full_type'], 'email') !== false) {
                    $rules[] = 'email';
                }
                break;
        }
        
        return $rules;
    }

    /**
     * Build custom validation rules for specific column names
     * (e.g., email, password patterns)
     */
    public static function buildCustomRules(string $column): array
    {
        $rules = [];
        
        // Email columns
        if (in_array($column, ['email', 'email_address', 'user_email'])) {
            $rules[] = 'email';
        }
        
        // Password columns
        if (in_array($column, ['password', 'password_hash'])) {
            $rules[] = 'min:8';
        }
        
        // Phone columns
        if (in_array($column, ['phone', 'telephone', 'mobile', 'phone_number'])) {
            $rules[] = 'regex:/^[0-9+\-\s()]+$/';
        }
        
        // URL columns
        if (in_array($column, ['url', 'website', 'link'])) {
            $rules[] = 'url';
        }
        
        return $rules;
    }
}