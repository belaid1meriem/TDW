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
// $router->post('/logout', 'AuthController', 'logout');


$router->get('/admin', 'AdminController', 'dashboard', 'admin');

// gestion des utilisateurs 
$router->get('/admin/users', 'UsersController', 'index', 'admin.users');
$router->get('/admin/users/create', 'UsersController', 'create', 'admin.users.create');
$router->post('/admin/users/store', 'UsersController', 'store', 'admin.users.store');
$router->get('/admin/users/edit/{id}', 'UsersController', 'edit', 'admin.users.edit');
$router->post('/admin/users/update/{id}', 'UsersController', 'update', 'admin.users.update');
$router->get('/admin/users/view/{id}', 'UsersController', 'view', 'admin.users.view');
$router->delete('/admin/users/delete/{id}', 'UsersController', 'delete', 'admin.users.delete');


$router->get('/admin/equipes', 'EquipesController', 'index', 'admin.equipes');
$router->get('/admin/equipes/view/{id}', 'EquipesController', 'view', 'admin.equipes.view');
// Run the application
$app->run();
