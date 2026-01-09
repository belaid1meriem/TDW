<?php

namespace Core\AutoCrud;

use Core\Router;

/**
 * RouteGenerator - Auto-registers CRUD routes for all database tables
 * 
 * Generates standard RESTful routes for each table:
 * - GET    /admin/{table}           -> index
 * - GET    /admin/{table}/create    -> create
 * - POST   /admin/{table}           -> store
 * - GET    /admin/{table}/{id}      -> show
 * - GET    /admin/{table}/{id}/edit -> edit
 * - POST   /admin/{table}/{id}      -> update (with _method=PUT)
 * - DELETE /admin/{table}/{id}      -> destroy
 */
class RouteGenerator
{
    private SchemaInspector $inspector;
    private array $config;

    public function __construct(array $config = [])
    {
        $this->inspector = new SchemaInspector();
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Register routes for all tables
     */
    public function registerAllTables(Router $router): void
    {
        $tables = $this->inspector->getTables($this->config['exclude_tables']);
        
        foreach ($tables as $table) {
            $this->registerTableRoutes($router, $table);
        }
    }

    /**
     * Register CRUD routes for a single table
     */
    public function registerTableRoutes(Router $router, string $table): void
    {
        $prefix = $this->config['route_prefix'];
        $controller = '\Core\AutoCrud\AutoCrudController'; // Full namespace
        
        // List all records
        $router->get(
            "{$prefix}/{$table}", 
            $controller, 
            'index', 
            "autocrud.{$table}.index"
        );
        
        // Show create form
        $router->get(
            "{$prefix}/{$table}/create", 
            $controller, 
            'create', 
            "autocrud.{$table}.create"
        );
        
        // Store new record
        $router->post(
            "{$prefix}/{$table}", 
            $controller, 
            'store', 
            "autocrud.{$table}.store"
        );
        
        // Show single record
        $router->get(
            "{$prefix}/{$table}/{id}", 
            $controller, 
            'show', 
            "autocrud.{$table}.show"
        );
        
        // Show edit form
        $router->get(
            "{$prefix}/{$table}/{id}/edit", 
            $controller, 
            'edit', 
            "autocrud.{$table}.edit"
        );
        
        // Update record (POST with _method=PUT)
        $router->post(
            "{$prefix}/{$table}/{id}", 
            $controller, 
            'update', 
            "autocrud.{$table}.update"
        );
        
        // Delete record
        $router->delete(
            "{$prefix}/{$table}/{id}", 
            $controller, 
            'destroy', 
            "autocrud.{$table}.destroy"
        );
    }

    /**
     * Register routes for specific tables only
     */
    public function registerTables(Router $router, array $tables): void
    {
        foreach ($tables as $table) {
            if ($this->inspector->tableExists($table)) {
                $this->registerTableRoutes($router, $table);
            }
        }
    }

    /**
     * Get list of all auto-registered routes
     */
    public function getRegisteredRoutes(): array
    {
        $tables = $this->inspector->getTables($this->config['exclude_tables']);
        $routes = [];
        
        foreach ($tables as $table) {
            $prefix = $this->config['route_prefix'];
            
            $routes[] = [
                'method' => 'GET',
                'path' => "{$prefix}/{$table}",
                'action' => 'index',
                'name' => "autocrud.{$table}.index"
            ];
            
            $routes[] = [
                'method' => 'GET',
                'path' => "{$prefix}/{$table}/create",
                'action' => 'create',
                'name' => "autocrud.{$table}.create"
            ];
            
            $routes[] = [
                'method' => 'POST',
                'path' => "{$prefix}/{$table}",
                'action' => 'store',
                'name' => "autocrud.{$table}.store"
            ];
            
            $routes[] = [
                'method' => 'GET',
                'path' => "{$prefix}/{$table}/{id}",
                'action' => 'show',
                'name' => "autocrud.{$table}.show"
            ];
            
            $routes[] = [
                'method' => 'GET',
                'path' => "{$prefix}/{$table}/{id}/edit",
                'action' => 'edit',
                'name' => "autocrud.{$table}.edit"
            ];
            
            $routes[] = [
                'method' => 'PUT',
                'path' => "{$prefix}/{$table}/{id}",
                'action' => 'update',
                'name' => "autocrud.{$table}.update"
            ];
            
            $routes[] = [
                'method' => 'DELETE',
                'path' => "{$prefix}/{$table}/{id}",
                'action' => 'destroy',
                'name' => "autocrud.{$table}.destroy"
            ];
        }
        
        return $routes;
    }

    /**
     * Default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'route_prefix' => '/admin',
            'exclude_tables' => [
                'migrations',
                'cache',
                'sessions',
                'jobs',
                'failed_jobs',
                'password_resets',
                'personal_access_tokens'
            ]
        ];
    }

    /**
     * Generate route helper methods
     * (for displaying available routes in debug mode)
     */
    public function printRouteList(): string
    {
        $routes = $this->getRegisteredRoutes();
        $output = "<h3>Auto-Generated Routes</h3><ul>";
        
        foreach ($routes as $route) {
            $output .= sprintf(
                "<li><strong>%s</strong> %s <em>(%s)</em></li>",
                $route['method'],
                $route['path'],
                $route['name']
            );
        }
        
        $output .= "</ul>";
        return $output;
    }
}