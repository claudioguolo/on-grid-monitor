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

$routes->get('history', 'Energy::historyForm');
$routes->post('history', 'Energy::importHistory', ['filter'=>'csrf']);

$routes->post('api/energia/bridge', 'Energy::receiveBridge');

$routes->post('api/energia/archive', 'Energy::receiveArchive');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate', ['filter'=>'csrf']);
$routes->post('logout', 'Auth::logout', ['filter'=>'csrf']);
