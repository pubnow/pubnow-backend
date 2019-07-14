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

Route::group(['namespace' => 'Api'], function () {

    // Authentication
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::get('me', 'AuthController@me')->middleware('auth');
    });

    // Category
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::resource('tags', 'TagController')->except(['create', 'edit']);
});
