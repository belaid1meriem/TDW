<?php

namespace Core;

class Router {
    private $routes = [];
    private $namedRoutes = [];
    private $middlewareMap = [
        // 'auth' => \App\Middleware\AuthMiddleware::class,
        // 'guest' => \App\Middleware\GuestMiddleware::class,
        // 'admin' => \App\Middleware\AdminMiddleware::class,
    ];
    
    public function get($path, $controller, $method, $name = null) {
        return $this->addRoute('GET', $path, $controller, $method, $name);
    }
    
    public function post($path, $controller, $method, $name = null) {
        return $this->addRoute('POST', $path, $controller, $method, $name);
    }
    
    public function put($path, $controller, $method, $name = null) {
        return $this->addRoute('PUT', $path, $controller, $method, $name);
    }
    
    public function delete($path, $controller, $method, $name = null) {
        return $this->addRoute('DELETE', $path, $controller, $method, $name);
    }
    
    private function addRoute($httpMethod, $path, $controller, $method, $name) {
        $route = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method,
            'regex' => $this->convertPathToRegex($path),
            'middleware' => []
        ];
        
        $this->routes[] = $route;
        $routeIndex = count($this->routes) - 1;
        
        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }
        
        return new RouteRegistrar($this, $routeIndex);
    }
    
    public function setMiddleware($routeIndex, $middleware) {
        if (isset($this->routes[$routeIndex])) {
            $this->routes[$routeIndex]['middleware'] = (array) $middleware;
        }
    }
    
    private function convertPathToRegex($path) {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $path . '$#';
    }
    
    public function dispatch(Request $request) {
        $requestMethod = $request->method();
        $requestPath = $request->path();
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            if (preg_match($route['regex'], $requestPath, $matches)) {

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $this->runMiddleware($route['middleware'], $request, function($request) use ($route, $params) {
                    $controllerClass = 'App\\Controllers\\' . $route['controller'];

                    if (!class_exists($controllerClass)) {
                        $this->handleError(500, "Controller not found: {$controllerClass}");
                        return;
                    }

                    $controller = new $controllerClass($request);
                    $action = $route['action'];
                    
                    if (!method_exists($controller, $action)) {
                        $this->handleError(500, "Method not found: {$action}");
                        return;
                    }
  
                    call_user_func_array([$controller, $action], $params);
                });
                
                return;
            }
        }
        
        $this->handleError(404, "Page not found");
    }

   
    
    private function runMiddleware($middleware, $request, $finalHandler) {
        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $middlewareName) use ($request) {
                $middlewareClass = $this->middlewareMap[$middlewareName] ?? null;
                
                if ($middlewareClass === null) {
                    throw new \Exception("Middleware not found: {$middlewareName}");
                }
                
                return function ($request) use ($middlewareClass, $next) {
                    $instance = new $middlewareClass();
                    return $instance->handle($request, $next);
                };
            },
            $finalHandler
        );
        
        return $pipeline($request);
    }
    
    public function url($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route not found: {$name}");
        }
        
        $path = $this->namedRoutes[$name]['path'];
        
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }
        
        return $path;
    }
    
    private function handleError($code, $message) {
        http_response_code($code);
        
        if ($code === 404) {
            // $view = new \App\Views\NotFoundView();
            // $view->render();
        } else {
            // $view = new \App\Views\ErrorView($message);
            // $view->render();
        }
    }
}
