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
//        Route::put('update/{user}', 'AuthController@update')->middleware('auth');
    });

    // User
    Route::resource('users', 'UserController')->except(['create', 'edit', 'store', 'delete']);

    // Category
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::resource('articles', 'ArticleController')->except(['create', 'edit']);

    // Search
    Route::group(['prefix' => 'search'], function () {
        Route::get('article', 'SearchController@query');
    });
});
