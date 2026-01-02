<?php

namespace Core;

abstract class View {
    protected $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    abstract public function render();
    
    protected function escape($value) {
        if (is_array($value)) {
            return array_map([$this, 'escape'], $value);
        }
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    protected function old($key, $default = '') {
        $oldData = Session::getFlash('old');
        return $oldData[$key] ?? $default;
    }
    
    protected function error($field) {
        $errors = Session::getFlash('errors');
        if (isset($errors[$field])) {
            return $errors[$field][0] ?? '';
        }
        return '';
    }
    
    protected function hasError($field) {
        $errors = Session::getFlash('errors');
        return isset($errors[$field]);
    }
    
    protected function flash($key) {
        return Session::getFlash($key);
    }
    
    protected function hasFlash($key) {
        return Session::hasFlash($key);
    }

}