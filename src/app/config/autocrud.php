<?php

/**
 * Auto-CRUD Configuration
 * 
 * Global settings for the automated CRUD system
 */

return [
    /**
     * Enable/disable auto-CRUD system
     */
    'enabled' => true,
    
    /**
     * Route prefix for auto-generated routes
     */
    'route_prefix' => '/admin',
    
    /**
     * Tables to exclude from auto-CRUD
     */
    'exclude_tables' => [
        'migrations',
        'cache',
        'sessions',
        'jobs',
        'failed_jobs',
        'password_resets',
        'password_reset_tokens',
        'personal_access_tokens',
    ],
    
    /**
     * Pagination settings
     */
    'items_per_page' => 20,
    
    /**
     * Default ordering
     */
    'default_order' => 'id DESC',
    
    /**
     * Enable debug mode (shows generated routes, etc.)
     */
    'debug' => false,
];