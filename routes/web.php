<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//statis

//Product
$router->get('/product', 'ProductController@index');
$router->post('/product/store', 'ProductController@store');

//Transactions
$router->get('/transaction', 'TransactionController@index');
$router->post('/transaction/store', 'TransactionController@store');

//dinamis

//Product
$router->patch('/product/update{id}', 'ProductController@update');
$router->delete('/product/delete{id}', 'ProductController@destroy');

//Transactions
$router->patch('/transaction/update{id}', 'TransactionController@update');
$router->delete('/transaction/delete{id}', 'TransactionController@destroy');