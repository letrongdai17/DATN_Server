<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::group(['guard' => 'admin'], function () {
    Route::post('/login', 'API\AuthController@login');
    Route::post('/register', 'API\AuthController@register');
});

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('/me', 'API\AuthController@me');
    Route::get('/classes', 'API\ClassesController@index');
    Route::get('/classes/{id}', 'API\ClassesController@getClassById');
});
