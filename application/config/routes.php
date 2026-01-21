<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Route khusus modul (opsional tapi rapi)
$route['login'] = 'auth/index';
$route['logout'] = 'auth/logout';

$route['role'] = 'master/role';
$route['role/(:any)'] = 'master/role/$1';

$route['user'] = 'master/user';
$route['user/(:any)'] = 'master/user/$1';

$route['permission'] = 'master/permission';
$route['permission/(:any)'] = 'master/permission/$1';

$route['module'] = 'master/module';
$route['module/(:any)'] = 'master/module/$1';

// Tambahkan ini PALING ATAS di bagian Route Role
$route['role/delete/(:any)'] = 'master/role/delete/$1';

// Baru route umum di bawahnya
$route['role'] = 'master/role';
$route['role/(:any)'] = 'master/role/$1';

/*
|--------------------------------------------------------------------------
| Route Module (Navigation) - Arahkan ke folder Master
|--------------------------------------------------------------------------
*/

// 1. Route Khusus untuk Delete (Agar parameter ID terbaca jelas)
$route['module/delete/(:any)'] = 'master/module/delete/$1';

// 2. Route Khusus untuk Save
$route['module/save'] = 'master/module/save';

// 3. Route Umum (Index/Halaman Utama)
$route['module'] = 'master/module';
$route['module/(:any)'] = 'master/module/$1';

/*
|--------------------------------------------------------------------------
| Route Module (Navigation) - Master
|--------------------------------------------------------------------------
*/
// 1. Route Khusus Aksi (Delete, Restore, Hard Delete)
$route['module/delete/(:any)']      = 'master/module/delete/$1';
$route['module/restore/(:any)']     = 'master/module/restore/$1';      // <-- Tambahkan ini
$route['module/hard_delete/(:any)'] = 'master/module/hard_delete/$1';  // <-- Tambahkan ini

// 2. Route Save
$route['module/save'] = 'master/module/save';

// 3. Route Umum (Index & Halaman Lain)
$route['module'] = 'master/module';
$route['module/(:any)'] = 'master/module/$1';

/*
|--------------------------------------------------------------------------
| Route Permission (Hak Akses)
|--------------------------------------------------------------------------
*/
// 1. Route untuk AJAX Change (Harus paling atas agar spesifik)
$route['permission/change'] = 'master/permission/change';

// 2. Route Halaman Utama
$route['permission'] = 'master/permission';
$route['permission/(:any)'] = 'master/permission/$1';
