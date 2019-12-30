<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/clients', 'ClientsController@index');

Route::middleware('auth:api')->post('/clients', 'ClientsController@store');

Route::middleware('auth:api')->delete('/clients/{id}', 'ClientsController@delete');

Route::middleware('auth:api')->get('/accounts/getCustomer', 'ClientsController@list');

Route::middleware('auth:api')->get('/accounts/{id}', 'AccountsController@index');

Route::middleware('auth:api')->post('/accounts', 'AccountsController@store');

Route::middleware('auth:api')->delete('/accounts/{id}', 'AccountsController@delete');

Route::middleware('auth:api')->get('/transactions/{id}', 'TransactionsController@index');

Route::post('/login', 'AuthController@login');

Route::middleware('auth:api')->post('/logout', 'AuthController@logout');
