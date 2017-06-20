<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

| There area two reserved routes:

|

|	$route['default_controller'] = 'welcome';

|

| This route indicates which controller class should be loaded if the

| URI contains no data. In the above example, the "welcome" class

| would be loaded.

|

|	$route['404_override'] = 'errors/page_missing';

|

| This route will tell the Router what URI segments to use if those provided

| in the URL cannot be matched to a valid route.

|

*/



$route['default_controller'] = "dashboard";

$route['404_override'] = 'error/error_404';

// routes for the api access
$route['api/(:any)/(:any)'] = 'api/$1/$2/';

/*$route['register']="register/index";
$route['register/(:any)']="register/$1";
$route['forgotpass']="forgotpass/index";*/

$route['register/(:any)/(:any)']='register/index';
$route['(:num)/payment-account'] = 'aosubscriptions/payment_account';

$route['(:num)/login'] = 'auth/login/$1/';

$route['(:num)/logout'] = 'auth/logout/';

$route['(:num)/(:any)'] = '$2/index/';

$route['(:num)/(:any)/(:any)'] = '$2/$3/';

$route['(:num)/(:any)/(:any)/(:any)'] = '$2/$3/$4/';

$route['(:num)/(:any)/(:any)/(:any)/(:any)'] = '$2/$3/$4/$5/';

$route['(:num)/(:any)/(:any)/(:any)/(:any)/(:any)'] = '$2/$3/$4/$5/$6/';



$route['subscriptions_cron'] = 'subscriptions_cron/index';

$route['subscription-invoices'] = 'subscriptioninvoices/index';

$route['(:num)'] = 'auth/login/$1/';

$route['login'] = 'auth/login';

//

$route['logout'] = 'auth/logout';

$route['dashboard'] = 'dashboard/dashboard';





/* End of file routes.php */

/* Location: ./application/config/routes.php */