<?php

use Core\Application;

require 'autoload.php';

$app = Application::getInstance();
$router = $app->router();

// Register routes
$router->get('/', 'HomeController', 'index', 'home');


// Run the application
$app->run();