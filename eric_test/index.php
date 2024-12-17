<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app/helpers/Const.php';
require_once __DIR__ . '/app/helpers/Function.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/app/utils/JWT.php';
require_once __DIR__ . '/app/utils/Response.php';
require_once __DIR__ . '/app/utils/Router.php';

$requestUri = trim($_SERVER['REQUEST_URI'], '/');
$requestMethod = $_SERVER['REQUEST_METHOD'];


Router::post('eric_test/api/register', 'AuthController@register');
Router::post('eric_test/api/login', 'AuthController@login');
Router::post('eric_test/api/product/create', 'ProductController@create',['auth']);
Router::get('eric_test/api/product', 'ProductController@detail',['auth']);
Router::get('eric_test/api/products/all', 'ProductController@listProduct',['auth']);
Router::post('eric_test/api/cart/add', 'CartController@addToCart',['auth']);
Router::get('eric_test/api/cart/view', 'CartController@viewCart',['auth']);
Router::post('eric_test/api/cart/checkout', 'CartController@checkoutCart',['auth']);
Router::get('eric_test/api/order/list', 'OrderController@listOrders',['auth']);
Router::put('eric_test/api/order/update-status', 'OrderController@updateStatus',['auth']);


Router::dispatch($requestUri, $requestMethod);