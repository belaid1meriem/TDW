<?php


namespace Core;

class Session {
    private static $started = false;
    
    public static function start() {
        if (self::$started) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function all() {
        self::start();
        return $_SESSION;
    }
    
    public static function clear() {
        self::start();
        $_SESSION = [];
    }
    
    public static function destroy() {
        self::start();
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        self::$started = false;
    }
    
    public static function flash($key, $value) {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }
    
    public static function getFlash($key, $default = null) {
        self::start();
        
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        
        return $default;
    }
    
    public static function hasFlash($key) {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
    
    public static function regenerate($deleteOld = true) {
        self::start();
        session_regenerate_id($deleteOld);
    }
    
    public static function getId() {
        self::start();
        return session_id();
    }
}