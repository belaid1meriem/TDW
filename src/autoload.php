<?php

spl_autoload_register(function ($class) {
    $prefixes = [
        'Core\\' => __DIR__ . '/core/',
        'Core\\AutoCrud\\' => __DIR__ . '/core/AutoCrud/',
        'Core\\AutoCrud\\Views\\' => __DIR__ . '/core/AutoCrud/Views/',
        'App\\Controllers\\' => __DIR__ . '/app/controllers/',
        'App\\Models\\' => __DIR__ . '/app/models/',
        'App\\Views\\' => __DIR__ . '/app/views/',
        'App\\Middleware\\' => __DIR__ . '/app/middleware/',
        'App\\' => __DIR__ . '/app/',
    ];
    
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});