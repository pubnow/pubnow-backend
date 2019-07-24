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
    Route::post('users/{user}/follow-organization', 'UserController@followOrganization');
    Route::post('users/{user}/unfollow-organization', 'UserController@unfollowOrganization');
    Route::post('users/{user}/follow-user', 'UserController@followUser');
    Route::post('users/{user}/unfollow-user', 'UserController@unfollowUser');
    Route::get('users/{user}/articles', 'UserController@articles');
    Route::resource('users', 'UserController')->except(['create', 'edit', 'delete']);

    // Category
    Route::get('categories/{user}/articles', 'CategoryController@articles');
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::get('tags/{user}/articles', 'TagController@articles');
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

    // Organization
    Route::post('organizations/{organization}/active', 'OrganizationController@active')->middleware('auth');
    Route::resource('organizations', 'OrganizationController')->except(['create', 'edit']);

    // InviteRequest
    Route::resource('invite-requests', 'InviteRequestController')->except(['create', 'edit']);
    // Comment
    Route::resource('comments', 'CommentController')->except(['index', 'show', 'create', 'edit']);
});
