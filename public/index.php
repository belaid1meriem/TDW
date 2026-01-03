<link rel="stylesheet" href="css/base.css">
<?php


use Core\Application;

require __DIR__ . '/../src/autoload.php';

$app = Application::getInstance();
$router = $app->router();


// Register routes
$router->get('/', 'HomeController', 'index', 'home');
$router->get('/login', 'AuthController', 'showLogin', 'login');
$router->post('/login', 'AuthController', 'login');
// $router->get('/register', 'AuthController', 'showRegister', 'register');
// $router->post('/register', 'AuthController', 'register');
// $router->post('/logout', 'AuthController', 'logout');

// Run the application
$app->run();