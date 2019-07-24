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
    Route::get('users/{user}/articles', 'UserController@articles');
    Route::resource('users', 'UserController')->except(['create', 'edit', 'delete']);

    // Category
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::get('articles/popular', 'ArticleController@popular');
    Route::get('articles/featured', 'ArticleController@featured');
    Route::resource('articles', 'ArticleController')->except(['create', 'edit']);

    // Role
    Route::resource('roles', 'RoleController')->except(['show', 'create', 'edit']);

    // Search
    Route::group(['prefix' => 'search'], function () {
        Route::get('article', 'SearchController@query');
    });

    // Clap
    Route::resource('claps', 'ClapController')->except(['index', 'show', 'create', 'edit', 'update'])->middleware('auth');

    // Comment
    Route::resource('comments', 'CommentController')->except(['index', 'show', 'create', 'edit']);
});
