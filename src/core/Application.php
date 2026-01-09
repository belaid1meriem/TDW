<?php

namespace Core;

use Core\AutoCrud\RouteGenerator;

class Application {
    private static $instance = null;
    private $router;
    private $request;
    private array $config = [];
    
    private function __construct() {
        $this->request = new Request();
        $this->router = new Router();
        Session::start();
        
        // Load configuration
        $this->loadConfig();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function router() {
        return $this->router;
    }
    
    public function request() {
        return $this->request;
    }
    
    public function config(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Load application configuration
     */
    private function loadConfig(): void {
        $configPath = __DIR__ . '/../app/config/autocrud.php';
        
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        }
    }
    
    /**
     * Bootstrap auto-CRUD routes
     */
    private function bootstrapAutoCrud(): void {
        // Check if auto-CRUD is enabled
        if (!($this->config['enabled'] ?? true)) {
            return;
        }
        
        try {
            $generator = new RouteGenerator($this->config);
            $generator->registerAllTables($this->router);
            
            // Debug mode: log registered routes (to error log, not output)
            if ($this->config['debug'] ?? false) {
                error_log("Auto-CRUD: Registered " . count($generator->getRegisteredRoutes()) . " routes");
            }
        } catch (\Exception $e) {
            error_log("Auto-CRUD bootstrap error: " . $e->getMessage());
            // Don't fail the entire application if auto-CRUD fails
        }
    }
    
    public function run() {
        try {
            // First, register auto-CRUD routes
            // These act as "fallback" routes - manual routes will take precedence
            $this->bootstrapAutoCrud();
            
            // Then dispatch the request
            $this->router->dispatch($this->request);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function handleException(\Exception $e) {
        http_response_code(500);
        
        // Check if debug mode is enabled
        $debug = $this->config['debug'] ?? false;
        
        if ($debug) {
            // In debug mode, show detailed error
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (line " . $e->getLine() . ")</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            // In production, show friendly error
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>An error occurred while processing your request. Please try again later.</p>";
        }
        
        // Always log the error
        error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    }
    
    /**
     * Get auto-CRUD route generator instance
     * Useful for debugging and testing
     */
    public function getRouteGenerator(): RouteGenerator {
        return new RouteGenerator($this->config);
    }
}