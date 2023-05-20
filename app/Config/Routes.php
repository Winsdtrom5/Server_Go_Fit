<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index', ['filter' => 'cors']);
$routes->resource('member', ['filter' => 'cors']);
$routes->resource('pegawai', ['filter' => 'cors']);
$routes->resource('jadwalumum', ['filter' => 'cors']);
$routes->resource('jadwalharian', ['filter' => 'cors']);
$routes->resource('instruktur', ['filter' => 'cors']);
$routes->resource('kelas', ['filter' => 'cors']);
$routes->resource('aktivasi', ['filter' => 'cors']);
$routes->resource('promopaket', ['filter' => 'cors']);
$routes->resource('promoreguler', ['filter' => 'cors']);
$routes->resource('depositkelas', ['filter' => 'cors']);
$routes->resource('deposituang', ['filter' => 'cors']);
$routes->resource('izininstruktur', ['filter' => 'cors']);
$routes->resource('bookingkelas', ['filter' => 'cors']);
$routes->resource('presensikelas', ['filter' => 'cors']);
$routes->resource('bookinggym', ['filter' => 'cors']);
$routes->resource('presensigym', ['filter' => 'cors']);
// $routes->get('member/(:num)', 'Member::showmember/$1', ['filter' => 'cors']);
// $routes->get('login', 'Login::index');
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
