<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index', ['filter' => 'guest']); // Página de login (no protegida)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'guest']); // Página de login (no protegida)
$routes->post('/api/auth/login', 'Auth::login'); // API de login (no protegida)
$routes->get('/api/auth/exists', 'Auth::exists'); // API para verificar usuarios (no protegida)
$routes->get('/api/auth/logOut', 'Auth::logOut'); // API de logout (puedes protegerla si quieres, pero no es necesario)
$routes->get('/api/auth/index', 'Auth::index');
// Rutas protegidas (requieren login)
$routes->get('/main', 'Main::main', ['filter' => 'auth']); // Página del dashboard protegida
$routes->get('/usuarios1', 'Usuarios1::usuarios1', ['filter' => 'auth']);
$routes->get('/roles', 'Roles::roles', ['filter' => 'auth']);
$routes->get('/perfil', 'Perfil::perfil', ['filter' => 'auth']);
$routes->get('/servicios', 'Servicios::servicios', ['filter' => 'auth']);
$routes->get('/ticket', 'Ticket::ticket', ['filter' => 'auth']);
// Agrega más rutas protegidas aquí, por ejemplo:
// $routes->get('/perfil', 'Usuario::perfil', ['filter' => 'auth']);

$routes->group('api/usuarios/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->get('index', 'Usuarios::index');
    $routes->post('readOne', 'Usuarios::readOne');
    $routes->post('create', 'Usuarios::create');
    $routes->post('update', 'Usuarios::update');
    $routes->post('delete', 'Usuarios::delete');
    $routes->post('search', 'Usuarios::search');
    $routes->post('deletelogic', 'Usuarios::deletelogic');
    $routes->post('updatePerfil', 'Usuarios::updatePerfil');
    //$routes->get('test', 'Usuarios::test');
    // RUTAS FALTANTES
    $routes->get('getTipo', 'Usuarios::getTipo');
    $routes->get('getEstado', 'Usuarios::getEstado');
});

$routes->group('api/notas/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->post('index', 'Notas::index');
     $routes->post('create', 'Notas::create');
      $routes->post('createRequest', 'Notas::createRequest');
});

$routes->group('api/tickets/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->get('index', 'Tickets::index');
    $routes->get('supporTickets', 'Tickets::supporTickets');
    $routes->get('userTickets', 'Tickets::userTickets');
    $routes->post('readOne', 'Tickets::readOne');
    $routes->post('create', 'Tickets::create');
    $routes->post('update', 'Tickets::update');
    $routes->post('delete', 'Tickets::delete');
    $routes->post('search', 'Tickets::search');
    $routes->post('deletelogic', 'Tickets::deletelogic');
    $routes->post('updatePerfil', 'Tickets::updatePerfil');
    //$routes->get('test', 'Usuarios::test');
    // RUTAS FALTANTES
    $routes->get('getServices', 'Tickets::getServices');
    $routes->get('getUsuarios', 'Tickets::getUsuarios');
});



$routes->group('api/services/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->get('index', 'Services::index');
    $routes->post('readOne', 'Services::readOne');
    $routes->post('create', 'Services::create');
    $routes->post('update', 'Services::update');
    $routes->post('delete', 'Services::delete');
    $routes->post('search', 'Services::search');
    $routes->post('deletelogic', 'Services::deletelogic');
    $routes->get('getTipo', 'Services::getTipo');
});

$routes->group('api/rolest/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->get('index', 'Rolest::index');
});




$routes->group('api/permisos/', ['namespace' => 'App\Controllers'], function($routes){
    $routes->post('readByRoleAndModule', 'Permisos::readByRoleAndModule');
    $routes->post('updateByRoleAndModule', 'Permisos::updateByRoleAndModule'); 
});







