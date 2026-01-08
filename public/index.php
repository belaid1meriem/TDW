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


// gestion des équipes
$router->get('/admin/equipes', 'EquipesController', 'index', 'admin.equipes');
$router->get('/admin/equipes/create', 'EquipesController', 'create', 'admin.equipes.create' );
$router->post('/admin/equipes/store', 'EquipesController', 'store', 'admin.equipes.store');
$router->get('/admin/equipes/view/{id}', 'EquipesController', 'view', 'admin.equipes.view');
$router->get('/admin/equipes/addMember/{equipeId}', 'EquipesController', 'addMember', 'admin.equipes.add-member');
$router->post('/admin/equipes/storeMember/{equipeId}', 'EquipesController', 'storeMember', 'admin.equipes.store-member');
$router->delete('/admin/equipes/removeMember/{equipeId}/{userId}', 'EquipesController', 'removeMember', 'admin.equipes.remove-member');

// gestion des projets
$router->get('/admin/projets', 'ProjetsController', 'index', 'admin.projets');
$router->get('/admin/projets/create', 'ProjetsController', 'create', 'admin.projets.create');
$router->post('/admin/projets/store', 'ProjetsController', 'store', 'admin.projets.store');
$router->get('/admin/projets/edit/{id}', 'ProjetsController', 'edit', 'admin.projets.edit');
$router->post('/admin/projets/update/{id}', 'ProjetsController', 'update', 'admin.projets.update');
$router->get('/admin/projets/view/{id}', 'ProjetsController', 'view', 'admin.projets.view');
$router->delete('/admin/projets/delete/{id}', 'ProjetsController', 'delete', 'admin.projets.delete');




// gestion des publications
$router->get('/admin/publications', 'PublicationsController', 'index', 'admin.publications');
$router->post('/admin/publications/validate/{id}', 'PublicationsController', 'validate', 'admin.publications.validate');
$router->post('/admin/publications/reject/{id}', 'PublicationsController', 'reject', 'admin.publications.reject');   


// Run the application
$app->run();
