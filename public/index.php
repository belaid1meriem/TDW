<?php
define('BASE_PATH', '/tdw/public');
define ('BASE_DIR', dirname(__DIR__));

use Core\Application;

require __DIR__ . '/../src/autoload.php';

$app = Application::getInstance();
$router = $app->router();


// Register routes
$router->get('/', 'HomeController', 'index', 'home');
$router->get('/login', 'AuthController', 'showLogin', 'login');
$router->post('/login', 'AuthController', 'login');
// $router->post('/logout', 'AuthController', 'logout');

$router->get('/admin', 'AdminController', 'dashboard', 'admin');




// Run the application
$app->run();
