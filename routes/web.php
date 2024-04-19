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

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

//statis

// STUFF
// struktur pemanggilan route-> method('/namapath', 'NamaController@namaFunction');
//route diurutkan berdasarkan path yg tdk dinamis lalu yg dinamis, diurutkan dgn garis miringnya dari terkecil
$router->get('/stuffs', 'StuffController@index');
$router->post('/stuffs/store', 'StuffController@store');
// softDeletes : trash, restore, undo
$router->get('/stuffs/trash', 'StuffController@trash');

// USER
$router->get('/users', 'UserController@index');
$router->post('/users/store', 'UserController@store');
$router->get('/users/trash', 'UserController@trash');

//INBOUND STUFF
$router->post('/inbound/store', 'InboundStuffController@store');
$router->get('/inbound/data', 'InboundStuffController@index');
$router->get('/inbound/trash', 'InboundStuffController@trash');

//LENDING
$router->post('/lending/store', 'LendingController@store');
$router->get('/lending/index', 'LendingController@index');
$router->get('/lending/trash', 'LendingController@trash');


//dinamis

//STUFF
$router->get('/stuffs/{id}', 'StuffController@show');
$router->patch('/stuffs/update/{id}', 'StuffController@update');
$router->delete('/stuffs/delete/{id}', 'StuffController@destroy');
$router->get('/stuffs/restore/{id}', 'StuffController@restore');
$router->get('/stuffs/permanen-delete/{id}', 'StuffController@permanenDelete');

//USER
$router->get('/users/{id}', 'UserController@show');
$router->patch('/users/update/{id}', 'UserController@update');
$router->delete('/users/delete/{id}', 'UserController@destroy');
$router->get('/users/restore/{id}', 'UserController@restore');
$router->get('/users/permanen-delete/{id}', 'UserController@permanenDelete');

//INBOUND 
$router->delete('/inbound/delete/{id}', 'InboundStuffController@destroy');
$router->get('/inbound/restore/{id}', 'InboundStuffController@restore');
$router->delete('/inbound/permanen-delete/{id}', 'InboundStuffController@permanenDelete');


