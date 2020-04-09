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
    Route::post('/login', 'API\auth\LoginController@login');
    Route::post('/register', 'API\auth\RegisterController@register');
});

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('/me', 'API\auth\MeController@me');
    Route::get('/classes', 'API\ClassesController@index');
    Route::post('/class/create', 'API\ClassesController@createClass');
});
