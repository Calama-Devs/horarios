<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

$routes->get('/horarios/publico', 'HorariosController::publico');

$routes->post('/horarios/getTurmasByCurso', 'HorariosController::getTurmasByCurso');
$routes->post('/horarios/filtrar', 'HorariosController::filtrar');

