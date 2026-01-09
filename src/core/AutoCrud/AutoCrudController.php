<?php

namespace Core\AutoCrud;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\AutoCrud\VirtualModel;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\ValidationBuilder;
use Core\AutoCrud\Views\ListView;
use Core\AutoCrud\Views\FormView;
use Core\AutoCrud\Views\ShowView;

/**
 * AutoCrudController - Generic controller that adapts to any table
 * 
 * This single controller handles CRUD operations for all tables
 * by extracting the table name from the route and using VirtualModel
 */
class AutoCrudController extends Controller
{
    protected VirtualModel $model;
    protected CrudEngine $engine;
    protected string $table;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        
        // Extract table name from route path
        $this->table = $this->extractTableFromRoute();
        
        // Load configuration if exists
        $config = $this->loadTableConfig($this->table);
        
        // Initialize model and engine
        $this->model = VirtualModel::fromTable($this->table, $config);
        $this->engine = new CrudEngine($this->model);
    }

    /**
     * List all records (with filters and pagination)
     * Route: GET /admin/{table}
     */
    public function index()
    {
        // Get filters from query params
        $filters = $this->request->query();
        unset($filters['page']); // Remove page from filters
        
        // Get pagination params
        $page = (int) ($this->request->query('page') ?? 1);
        $perPage = 20;
        
        // Get sorting
        $orderBy = $this->request->query('order_by', '');
        
        // Fetch data
        $result = $this->engine->list($filters, $page, $perPage, $orderBy);
        
        // Render view
        $view = new ListView($this->model, $result);
        $this->render($view);
    }

    /**
     * Show single record details
     * Route: GET /admin/{table}/{id}
     */
    public function show($id)
    {
        $record = $this->engine->show($id);
        
        if (!$record) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Record not found."
            );
            return;
        }
        
        $view = new ShowView($this->model, $record);
        $this->render($view);
    }

    /**
     * Show create form
     * Route: GET /admin/{table}/create
     */
    public function create()
    {
        $view = new FormView($this->model, mode: 'create');
        $this->render($view);
    }

    /**
     * Store new record
     * Route: POST /admin/{table}
     */
    public function store()
    {
        // Build validation rules from model
        $rules = ValidationBuilder::buildFromModel($this->model);
        
        // Validate input
        $validator = Validator::make($this->request->all(), $rules);
        
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }
        
        // Create record
        $id = $this->engine->create($validator->validated());
        
        if ($id === false) {
            Session::flash('error', 'Failed to create record. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }
        
        $this->redirectWithSuccess(
            BASE_PATH . "/admin/{$this->table}", 
            "Record created successfully!"
        );
    }

    /**
     * Show edit form
     * Route: GET /admin/{table}/{id}/edit
     */
    public function edit($id)
    {
        $record = $this->engine->show($id);
        
        if (!$record) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Record not found."
            );
            return;
        }
        
        $view = new FormView($this->model, $record, 'edit');
        $this->render($view);
    }

    /**
     * Update existing record
     * Route: PUT /admin/{table}/{id}
     */
    public function update($id)
    {
        // Check if record exists
        $record = $this->engine->show($id);
        
        if (!$record) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Record not found."
            );
            return;
        }
        
        // Build validation rules (with id exception for unique fields)
        $rules = ValidationBuilder::buildFromModel($this->model, $id);
        
        // Validate input
        $validator = Validator::make($this->request->all(), $rules);
        
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }
        
        // Update record
        $success = $this->engine->update($id, $validator->validated());
        
        if (!$success) {
            Session::flash('error', 'Failed to update record. Please try again.');
            Session::flash('old', $this->request->all());
            $this->back();
            return;
        }
        
        $this->redirectWithSuccess(
            BASE_PATH . "/admin/{$this->table}", 
            "Record updated successfully!"
        );
    }

    /**
     * Delete record
     * Route: DELETE /admin/{table}/{id}
     */
    public function destroy($id)
    {
        // Check if record exists
        $record = $this->engine->show($id);
        
        if (!$record) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Record not found."
            );
            return;
        }
        
        // Check if delete is allowed for this table
        $config = $this->loadTableConfig($this->table);
        if ($config['disable_delete'] ?? false) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Delete operation is disabled for this table."
            );
            return;
        }
        
        // Delete record
        $success = $this->engine->delete($id);
        
        if (!$success) {
            $this->redirectWithError(
                BASE_PATH . "/admin/{$this->table}", 
                "Failed to delete record."
            );
            return;
        }
        
        $this->redirectWithSuccess(
            BASE_PATH . "/admin/{$this->table}", 
            "Record deleted successfully!"
        );
    }

    /**
     * Extract table name from current route
     */
    protected function extractTableFromRoute(): string
    {
        $path = $this->request->path();
        
        // Pattern: /admin/{table}[/...]
        if (preg_match('#^/admin/([a-z_]+)#', $path, $matches)) {
            return $matches[1];
        }
        
        throw new \Exception("Could not extract table name from route: {$path}");
    }

    /**
     * Load table-specific configuration
     */
    protected function loadTableConfig(string $table): array
    {
        $configFile = BASE_DIR . '/src/app/config/tables.php';
        
        if (!file_exists($configFile)) {
            return [];
        }

        
        $allConfig = require $configFile;

        $defaultConfig = $allConfig['default'] ?? [];
        $tableConfig = $allConfig[$table] ?? [];
        $allConfig[$table] = $tableConfig + $defaultConfig;

        return $allConfig[$table] ?? [];
    }
}