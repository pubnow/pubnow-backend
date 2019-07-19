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

    // User
    Route::resource('users', 'UserController')->except(['create', 'edit', 'delete']);

    // Category
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::resource('articles', 'ArticleController')->except(['create', 'edit']);
    Route::get('popular-articles', 'ArticleController@popular');

    // Search
    Route::group(['prefix' => 'search'], function () {
        Route::get('article', 'SearchController@query');
    });

    // Clap
    Route::resource('claps', 'ClapController')->except(['index', 'create', 'edit', 'update'])->middleware('auth');
});
