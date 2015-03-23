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
|	http://codeigniter.com/user_guide/general/routing.html
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
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//CONSTANTS
$settings['home'] = 'home';
$settings['auth_class'] = 'authentication';



$route['auth'] = "restful_controller/auth";

//eventually there will be no /read, /create, /update, /delete
//It will be replaced with an actual RESTful service that
//identifies POST, GET, PUT, DELETE, HEAD, PATCH and figures
//what to do.
//
$route['api/v1.0/(:any)'] = "restful_controller/handler/$1";
$route['api/v1.0/(:any)/(:any)'] = "restful_controller/handler/$1/$2";
$route['api/v1.0/(:any)/(:any)/(:any)'] = "restful_controller/handler/$1/$2/$3";
$route['api/v1.0/(:any)/(:any)/(:any)/(:any)'] = "restful_controller/handler/$1/$2/$3/$4";
//$route['(:any)/(:any)'] = "rest_controller/handler/$1/$2";
//$route['(:any)/(:any)/(:any)'] = "rest_controller/handler/$1/$2/$3";

$route['info/(:any)'] = "view_controller/info/$1";

//test
$route['test'] = "view_controller/test";
$route['test2'] = "view_controller/test2";

//login
//$route['login'] = "view_controller/login/{$settings['home']}";
//$route['login'] = "view_controller/loadPage/home";
$route['login'] = "ui_controller/loadPage/home";

//user home
//$route['home'] = "view_controller/loadPage/home";
$route['home'] = "ui_controller/loadPage/home";

//facebook example: facebook.com/hey.marco.chang
//$route['(any)'] = "view_controller/loadPage/profile/$1"

//signup
//$route['register'] = "view_controller/factory_view/register";
//
$route['register'] = "view_controller/factory_view/register/user";

//cant login
$route['forgot'] = "view_controller/factory_view/forgot";

//login
$route['thank-you'] = "view_controller/factory_view/thank-you";

//new-user
$route['new-user'] = "view_controller/factory_create/thank-you/user";

//admin home
$route['admin'] = "view_controller/factory_view/admin";

/* End of file routes.php */
/* Location: ./application/config/routes.php */
