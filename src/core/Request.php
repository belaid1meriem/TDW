<?php
namespace Core;

class Request {
    private $query;
    private $post;
    private $server;
    private $files;
    private $cookies;
    
    public function __construct() {
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
    }
    
    public function method() {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        
        if ($method === 'POST' && isset($this->post['_method'])) {
            return strtoupper($this->post['_method']);
        }
        
        return $method;
    }
    
    public function path() {
        $path = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path === '' ? '/' : $path;
    }
    
    public function input($key, $default = null) {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }
    
    public function all() {
        return array_merge($this->query, $this->post);
    }
    
    public function only($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();
        $all = $this->all();
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }
        
        return $result;
    }
    
    public function except($keys) {
        $keys = is_array($keys) ? $keys : func_get_args();
        $all = $this->all();
        
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        
        return $all;
    }
    
    public function has($key) {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }
    
    public function query($key = null, $default = null) {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }
    
    public function file($key) {
        return $this->files[$key] ?? null;
    }
    
    public function hasFile($key) {
        if (!isset($this->files[$key])) {
            return false;
        }
        
        $file = $this->files[$key];
        return isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
    }
    
    public function cookie($key, $default = null) {
        return $this->cookies[$key] ?? $default;
    }
    
    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
    
    public function isGet() {
        return $this->method() === 'GET';
    }
    
    public function isPost() {
        return $this->method() === 'POST';
    }
    
    public function isPut() {
        return $this->method() === 'PUT';
    }
    
    public function isDelete() {
        return $this->method() === 'DELETE';
    }
    
    public function ip() {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        }
        
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $this->server['HTTP_X_FORWARDED_FOR'])[0];
        }
        
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function url() {
        $protocol = (isset($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . $this->path();
    }
}