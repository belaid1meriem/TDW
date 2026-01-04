<?php
define('BASE_PATH', '/tdw/public');

use Core\Application;

require __DIR__ . '/../src/autoload.php';

$app = Application::getInstance();
$router = $app->router();


// Register routes
$router->get('/', 'HomeController', 'index', 'home');
$router->get('/login', 'AuthController', 'showLogin', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/admin', 'AdminController', 'dashboard', 'admin');
$router->get('/admin/users', 'UsersController', 'index', 'admin.users');
// $router->get('/register', 'AuthController', 'showRegister', 'register');
// $router->post('/register', 'AuthController', 'register');
// $router->post('/logout', 'AuthController', 'logout');

// Run the application
$app->run();
