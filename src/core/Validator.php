<?php

namespace Core;

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    private $validatedData = [];
    private $customMessages = [];
    
    private function __construct($data, $rules, $customMessages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }
    
    public static function make($data, $rules, $customMessages = []) {
        $validator = new self($data, $rules, $customMessages);
        $validator->validate();
        return $validator;
    }
    
    public function validate() {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }
            
            foreach ($fieldRules as $rule) {
                if ($this->applyRule($field, $value, $rule) === false) {
                    // Stop processing rules for this field after first failure
                    break;
                }
            }
            
            if (!isset($this->errors[$field])) {
                $this->validatedData[$field] = $value;
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule($field, $value, $rule) {
        $rule = trim($rule);
        
        if (strpos($rule, ':') !== false) {
            list($ruleName, $ruleValue) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }
        
        $ruleName = strtolower(trim($ruleName));
        
        try {
            switch ($ruleName) {
                case 'required':
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $this->addError($field, "The $field field is required.");
                        return false;
                    }
                    break;
                    
                case 'email':
                    if ($this->hasValue($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "The $field must be a valid email address.");
                        return false;
                    }
                    break;
                    
                case 'min':
                    if (!is_numeric($ruleValue)) {
                        throw new \InvalidArgumentException("Min rule requires a numeric value");
                    }
                    if ($this->hasValue($value) && mb_strlen((string)$value) < (int)$ruleValue) {
                        $this->addError($field, "The $field must be at least $ruleValue characters.");
                        return false;
                    }
                    break;
                    
                case 'max':
                    if (!is_numeric($ruleValue)) {
                        throw new \InvalidArgumentException("Max rule requires a numeric value");
                    }
                    if ($this->hasValue($value) && mb_strlen((string)$value) > (int)$ruleValue) {
                        $this->addError($field, "The $field must not exceed $ruleValue characters.");
                        return false;
                    }
                    break;
                    
                case 'numeric':
                    if ($this->hasValue($value) && !is_numeric($value)) {
                        $this->addError($field, "The $field must be a number.");
                        return false;
                    }
                    break;
                    
                case 'integer':
                    if ($this->hasValue($value)) {
                        if (filter_var($value, FILTER_VALIDATE_INT) === false && $value !== 0 && $value !== '0') {
                            $this->addError($field, "The $field must be an integer.");
                            return false;
                        }
                    }
                    break;
                    
                case 'alpha':
                    if ($this->hasValue($value) && !ctype_alpha(str_replace(' ', '', (string)$value))) {
                        $this->addError($field, "The $field must contain only letters.");
                        return false;
                    }
                    break;
                    
                case 'alphanumeric':
                    if ($this->hasValue($value) && !ctype_alnum(str_replace(' ', '', (string)$value))) {
                        $this->addError($field, "The $field must contain only letters and numbers.");
                        return false;
                    }
                    break;
                    
                case 'confirmed':
                    $confirmField = $field . '_confirmation';
                    $confirmValue = $this->data[$confirmField] ?? null;
                    if ($value !== $confirmValue) {
                        $this->addError($field, "The $field confirmation does not match.");
                        return false;
                    }
                    break;
                    
                case 'unique':
                    if (!$this->validateDatabaseRule($field, $value, $ruleValue, 'unique')) {
                        return false;
                    }
                    break;
                    
                case 'exists':
                    if (!$this->validateDatabaseRule($field, $value, $ruleValue, 'exists')) {
                        return false;
                    }
                    break;
                    
                case 'in':
                    if ($this->hasValue($value)) {
                        $allowedValues = array_map('trim', explode(',', $ruleValue));
                        if (!in_array($value, $allowedValues, true)) {
                            $this->addError($field, "The selected $field is invalid.");
                            return false;
                        }
                    }
                    break;
                    
                case 'url':
                    if ($this->hasValue($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($field, "The $field must be a valid URL.");
                        return false;
                    }
                    break;
                    
                case 'date':
                    if ($this->hasValue($value)) {
                        $date = \DateTime::createFromFormat('Y-m-d', $value);
                        if (!$date || $date->format('Y-m-d') !== $value) {
                            $this->addError($field, "The $field must be a valid date (Y-m-d).");
                            return false;
                        }
                    }
                    break;
                    
                case 'same':
                    $otherField = trim($ruleValue);
                    $otherValue = $this->data[$otherField] ?? null;
                    if ($value !== $otherValue) {
                        $this->addError($field, "The $field must match $otherField.");
                        return false;
                    }
                    break;
                    
                case 'different':
                    $otherField = trim($ruleValue);
                    $otherValue = $this->data[$otherField] ?? null;
                    if ($value === $otherValue) {
                        $this->addError($field, "The $field must be different from $otherField.");
                        return false;
                    }
                    break;
                    
                case 'regex':
                    if ($this->hasValue($value) && !preg_match($ruleValue, $value)) {
                        $this->addError($field, "The $field format is invalid.");
                        return false;
                    }
                    break;
                    
                case 'boolean':
                    $booleanValues = [true, false, 0, 1, '0', '1', 'true', 'false'];
                    if ($this->hasValue($value) && !in_array($value, $booleanValues, true)) {
                        $this->addError($field, "The $field must be true or false.");
                        return false;
                    }
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Unknown validation rule: $ruleName");
            }
        } catch (\Exception $e) {
            $this->addError($field, "Validation error: " . $e->getMessage());
            return false;
        }
        
        return true;
    }
    
    private function validateDatabaseRule($field, $value, $ruleValue, $type) {
        if (!$this->hasValue($value)) {
            return true;
        }
        
        $parts = array_map('trim', explode(',', $ruleValue));
        
        if (count($parts) < 2) {
            throw new \InvalidArgumentException("The '$type' rule requires table and column (format: table,column or table,column,ignoreId)");
        }
        
        $table = $parts[0];
        $column = $parts[1];
        $ignoreId = $parts[2] ?? null;
        $ignoreColumn = $parts[3] ?? 'id';
        
        // Security: validate table and column names format to prevent SQL injection
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) || 
            !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column) ||
            ($ignoreColumn && !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $ignoreColumn))) {
            throw new \InvalidArgumentException("Invalid table or column name format");
        }
        
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = ?";
            $params = [$value];
            
            // Handle "ignore" parameter for unique rule (useful for updates)
            if ($ignoreId !== null && $type === 'unique') {
                $sql .= " AND `$ignoreColumn` != ?";
                $params[] = $ignoreId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetchColumn();
            
            if ($type === 'unique' && $count > 0) {
                $this->addError($field, "The $field has already been taken.");
                return false;
            }
            
            if ($type === 'exists' && $count == 0) {
                $this->addError($field, "The selected $field is invalid.");
                return false;
            }
            
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database validation error: " . $e->getMessage());
        }
        
        return true;
    }
    
    private function hasValue($value) {
        return $value !== null && $value !== '';
    }
    
    private function addError($field, $message) {
        // Check for custom message
        if (isset($this->customMessages["$field"])) {
            $message = $this->customMessages["$field"];
        }
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    public function fails() {
        return !empty($this->errors);
    }
    
    public function passes() {
        return empty($this->errors);
    }
    
    public function errors() {
        return $this->errors;
    }
    
    public function firstError($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        
        return null;
    }
    
    public function validated() {
        return $this->validatedData;
    }
}