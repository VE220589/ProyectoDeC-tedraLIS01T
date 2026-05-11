<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rutas publicas: login tradicional, login con Google y verificacion inicial.
$routes->get('/', 'Dashboard::index', ['filter' => 'guest']);
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'guest']);
$routes->post('/api/auth/login', 'Auth::login');
$routes->post('/api/auth/google', 'Auth::google');
$routes->get('/api/auth/exists', 'Auth::exists');

// Rutas de autenticacion protegidas: requieren sesion iniciada.
$routes->get('/api/auth/logOut', 'Auth::logOut', ['filter' => 'auth']);
$routes->get('/api/auth/index', 'Auth::index', ['filter' => 'auth:users.view']);

// Vistas principales protegidas. Algunas solo piden sesion y otras permisos RBAC.
$routes->get('/main', 'Main::main', ['filter' => 'auth']);
$routes->get('/usuarios1', 'Usuarios1::usuarios1', ['filter' => 'auth:users.view']);
$routes->get('/roles', 'Roles::roles', ['filter' => 'auth:roles.view']);
$routes->get('/perfil', 'Perfil::perfil', ['filter' => 'auth']);
$routes->get('/servicios', 'Servicios::servicios', ['filter' => 'auth:services.view']);
$routes->get('/ticket', 'Ticket::ticket', ['filter' => 'auth:tickets.view']);

// API de usuarios: CRUD administrativo y lectura/actualizacion del perfil propio.
$routes->group('api/usuarios/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('index', 'Usuarios::index', ['filter' => 'auth:users.view']);
    $routes->post('readOne', 'Usuarios::readOne', ['filter' => 'auth:users.view']);
    $routes->get('readPerfil', 'Usuarios::readPerfil', ['filter' => 'auth']);
    $routes->post('create', 'Usuarios::create', ['filter' => 'auth:users.create']);
    $routes->post('update', 'Usuarios::update', ['filter' => 'auth:users.update']);
    $routes->post('delete', 'Usuarios::delete', ['filter' => 'auth:users.delete']);
    $routes->post('search', 'Usuarios::search', ['filter' => 'auth:users.view']);
    $routes->post('deletelogic', 'Usuarios::deletelogic', ['filter' => 'auth:users.delete']);
    $routes->post('updatePerfil', 'Usuarios::updatePerfil', ['filter' => 'auth']);
    $routes->get('getTipo', 'Usuarios::getTipo', ['filter' => 'auth:users.view']);
    $routes->get('getEstado', 'Usuarios::getEstado', ['filter' => 'auth:users.view']);
});

// API de notas: registra comentarios y solicitudes de cierre en el historial del ticket.
$routes->group('api/notas/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->post('index', 'Notas::index', ['filter' => 'auth:tickets.view']);
    $routes->post('create', 'Notas::create', ['filter' => 'auth:tickets.update']);
    $routes->post('createRequest', 'Notas::createRequest', ['filter' => 'auth:tickets.update']);
});

// API de tickets: separa bandejas por rol y protege cada accion con permisos.
$routes->group('api/tickets/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('index', 'Tickets::index', ['filter' => 'auth:tickets.view']);
    $routes->get('supporTickets', 'Tickets::supporTickets', ['filter' => 'auth:tickets.view']);
    $routes->get('userTickets', 'Tickets::userTickets', ['filter' => 'auth:tickets.view']);
    $routes->post('readOne', 'Tickets::readOne', ['filter' => 'auth:tickets.view']);
    $routes->post('create', 'Tickets::create', ['filter' => 'auth:tickets.create']);
    $routes->post('update', 'Tickets::update', ['filter' => 'auth:tickets.update']);
    $routes->post('delete', 'Tickets::delete', ['filter' => 'auth:tickets.delete']);
    $routes->post('search', 'Tickets::search', ['filter' => 'auth:tickets.view']);
    $routes->post('deletelogic', 'Tickets::deletelogic', ['filter' => 'auth:tickets.delete']);
    $routes->post('updatePerfil', 'Tickets::updatePerfil', ['filter' => 'auth:tickets.update']);
    $routes->get('getServices', 'Tickets::getServices', ['filter' => 'auth:tickets.view']);
    $routes->get('getUsuarios', 'Tickets::getUsuarios', ['filter' => 'auth:tickets.view']);
});

// API del catalogo de servicios de TI.
$routes->group('api/services/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('index', 'Services::index', ['filter' => 'auth:services.view']);
    $routes->post('readOne', 'Services::readOne', ['filter' => 'auth:services.view']);
    $routes->post('create', 'Services::create', ['filter' => 'auth:services.create']);
    $routes->post('update', 'Services::update', ['filter' => 'auth:services.update']);
    $routes->post('delete', 'Services::delete', ['filter' => 'auth:services.delete']);
    $routes->post('search', 'Services::search', ['filter' => 'auth:services.view']);
    $routes->post('deletelogic', 'Services::deletelogic', ['filter' => 'auth:services.delete']);
    $routes->get('getTipo', 'Services::getTipo', ['filter' => 'auth:services.view']);
});

// API auxiliar para listar roles.
$routes->group('api/rolest/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('index', 'Rolest::index', ['filter' => 'auth:roles.view']);
});

// API de permisos: permite consultar y actualizar permisos por rol/modulo.
$routes->group('api/permisos/', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->post('readByRoleAndModule', 'Permisos::readByRoleAndModule', ['filter' => 'auth:roles.view']);
    $routes->post('updateByRoleAndModule', 'Permisos::updateByRoleAndModule', ['filter' => 'auth:roles.update']);
});
