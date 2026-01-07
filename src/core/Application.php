<?php


namespace Core;

class Application {
    private static $instance = null;
    private $router;
    private $request;
    
    private function __construct() {
        $this->request = new Request();
        $this->router = new Router();
        Session::start();
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
    
    public function run() {
        try {
            $this->router->dispatch($this->request);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function handleException(\Exception $e) {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo $e->getMessage() . "\n" . $e->getTraceAsString();
    }
}