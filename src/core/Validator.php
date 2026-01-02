<?php

namespace Core;

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    private $validatedData = [];
    
    private function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public static function make($data, $rules) {
        $validator = new self($data, $rules);
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
                $this->applyRule($field, $value, $rule);
            }
            
            if (!isset($this->errors[$field])) {
                $this->validatedData[$field] = $value;
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule($field, $value, $rule) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $ruleValue) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "The $field field is required.");
                }
                break;
                
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The $field must be a valid email address.");
                }
                break;
                
            case 'min':
                if ($value !== null && strlen($value) < $ruleValue) {
                    $this->addError($field, "The $field must be at least $ruleValue characters.");
                }
                break;
                
            case 'max':
                if ($value !== null && strlen($value) > $ruleValue) {
                    $this->addError($field, "The $field must not exceed $ruleValue characters.");
                }
                break;
                
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, "The $field must be a number.");
                }
                break;
                
            case 'integer':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "The $field must be an integer.");
                }
                break;
                
            case 'alpha':
                if ($value !== null && $value !== '' && !ctype_alpha($value)) {
                    $this->addError($field, "The $field must contain only letters.");
                }
                break;
                
            case 'alphanumeric':
                if ($value !== null && $value !== '' && !ctype_alnum($value)) {
                    $this->addError($field, "The $field must contain only letters and numbers.");
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                $confirmValue = $this->data[$confirmField] ?? null;
                if ($value !== $confirmValue) {
                    $this->addError($field, "The $field confirmation does not match.");
                }
                break;
                
            case 'unique':
                list($table, $column) = explode(',', $ruleValue);
                if (!$this->checkUnique($table, $column, $value)) {
                    $this->addError($field, "The $field has already been taken.");
                }
                break;
                
            case 'exists':
                list($table, $column) = explode(',', $ruleValue);
                if (!$this->checkExists($table, $column, $value)) {
                    $this->addError($field, "The selected $field is invalid.");
                }
                break;
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if ($value !== null && !in_array($value, $allowedValues)) {
                    $this->addError($field, "The selected $field is invalid.");
                }
                break;
                
            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "The $field must be a valid URL.");
                }
                break;
                
            case 'date':
                if ($value !== null && $value !== '') {
                    $date = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        $this->addError($field, "The $field must be a valid date.");
                    }
                }
                break;
                
            case 'same':
                $otherField = $ruleValue;
                $otherValue = $this->data[$otherField] ?? null;
                if ($value !== $otherValue) {
                    $this->addError($field, "The $field must match $otherField.");
                }
                break;
                
            case 'different':
                $otherField = $ruleValue;
                $otherValue = $this->data[$otherField] ?? null;
                if ($value === $otherValue) {
                    $this->addError($field, "The $field must be different from $otherField.");
                }
                break;
        }
    }
    
    private function checkUnique($table, $column, $value) {
        if ($value === null || $value === '') {
            return true;
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$value]);
        $count = $stmt->fetchColumn();
        
        return $count == 0;
    }
    
    private function checkExists($table, $column, $value) {
        if ($value === null || $value === '') {
            return true;
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$value]);
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }
    
    private function addError($field, $message) {
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
    
    public function validated() {
        return $this->validatedData;
    }
}