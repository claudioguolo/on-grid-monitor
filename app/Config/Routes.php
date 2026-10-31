<?php
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'Energy::index');
$routes->get('health', 'Energy::health');
$routes->match(['get','post'], 'api/energia/medidor', 'Energy::receive');
$routes->get('api/settings', 'Energy::settings');
$routes->get('api/readings', 'Energy::readings');
$routes->get('api/power', 'Energy::power');
$routes->get('api/technical', 'Energy::technical');
$routes->get('api/summary', 'Energy::summary');
$routes->get('api/daily', 'Energy::daily');

$routes->get('history', 'Energy::historyForm');
$routes->post('history', 'Energy::importHistory', ['filter'=>'csrf']);

$routes->post('api/energia/bridge', 'Energy::receiveBridge');

$routes->post('api/energia/archive', 'Energy::receiveArchive');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate', ['filter'=>'csrf']);
$routes->post('logout', 'Auth::logout', ['filter'=>'csrf']);

$routes->get('admin', 'Admin::users');
$routes->get('admin/users', 'Admin::users');
$routes->get('admin/users/new', 'Admin::edit');
$routes->get('admin/users/(:num)', 'Admin::edit/$1');
$routes->post('admin/users', 'Admin::saveUser', ['filter'=>'csrf']);
$routes->post('admin/users/(:num)', 'Admin::saveUser/$1', ['filter'=>'csrf']);
$routes->get('admin/settings', 'Admin::settings');
$routes->post('admin/settings', 'Admin::saveSettings', ['filter'=>'csrf']);
$routes->get('admin/audit', 'Admin::audit');
