<?php
namespace Core;

class RouteRegistrar {
    private $router;
    private $routeIndex;
    
    public function __construct(Router $router, $routeIndex) {
        $this->router = $router;
        $this->routeIndex = $routeIndex;
    }
    
    public function middleware($middleware) {
        $this->router->setMiddleware($this->routeIndex, $middleware);
        return $this;
    }
}

