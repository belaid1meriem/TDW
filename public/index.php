<?php
define('BASE_PATH', '/tdw/public');
define ('BASE_DIR', dirname(__DIR__));

use Core\Application;

require __DIR__ . '/../src/autoload.php';

$app = Application::getInstance();
$router = $app->router();


// Register routes

$router->get('/login', 'AuthController', 'showLogin', 'login');
$router->post('/login', 'AuthController', 'login');
$router->post('/logout', 'AuthController', 'logout');

$router->get('/admin', 'AdminController', 'dashboard', 'admin');
$router->get('/profile', 'ProfileController', 'index', 'profile');

$router->get('/', 'HomeController', 'index', 'home');
$router->get('/projets', 'ProjetController', 'index', 'projets');
$router->get('/publications', 'PublicationController', 'index', 'publications');
$router->get('/projets/{id}', 'ProjetController', 'show', 'projet_show');
$router->get('/publications/{id}', 'PublicationController', 'show', 'publication_show');



// Run the application
$app->run();
