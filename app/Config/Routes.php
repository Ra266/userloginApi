<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('sign', 'UserController::userSign');
$routes->post('login', 'UserController::login');
$routes->post('profile', 'UserController::profile');
