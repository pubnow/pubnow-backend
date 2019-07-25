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
    Route::group(['prefix' => 'users'], function () {

        // follow and unfollow user
        Route::post('follow', 'UserController@follow');
        Route::delete('follow', 'UserController@unfollow');


        Route::get('{user}/users-followed', 'UserController@getUsersFollowed');
        Route::get('{user}/followers', 'UserController@getFollowers');
        Route::get('{user}/organizations-followed', 'UserController@getOrganizationsFollowed');

        Route::get('{user}/articles', 'UserController@articles');
    });
    Route::resource('users', 'UserController')->except(['create', 'edit']);

    // Category
    Route::get('categories/{category}/articles', 'CategoryController@articles');
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::get('tags/{tag}/articles', 'TagController@articles');
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::get('articles/popular', 'ArticleController@popular');
    Route::get('articles/featured', 'ArticleController@featured');
    Route::resource('articles', 'ArticleController')->except(['create', 'edit']);

    // Role
    Route::resource('roles', 'RoleController')->except(['show', 'create', 'edit']);

    // Search
    Route::group(['prefix' => 'search'], function () {
        Route::get('article', 'SearchController@article');
    });

    // Clap
    Route::resource('claps', 'ClapController')->except(['index', 'show', 'create', 'edit', 'update'])->middleware('auth');

    // Organization
    // follow and unfollow organization
    Route::post('organizations/follow', 'OrganizationController@follow');
    Route::delete('organizations/follow', 'OrganizationController@unfollow');
    Route::post('organizations/{organization}/active', 'OrganizationController@active');
    Route::get('organizations/{organization}/followers', 'OrganizationController@getFollowers');
    Route::resource('organizations', 'OrganizationController')->except(['create', 'edit']);

    // InviteRequest
    Route::resource('invite-requests', 'InviteRequestController')->except(['create', 'edit']);

    // Comment
    Route::resource('comments', 'CommentController')->except(['index', 'show', 'create', 'edit']);
});
