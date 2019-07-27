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
    Route::get('users/bookmarks', 'UserController@bookmarks');
    Route::get('users/{user}/articles', 'UserController@articles');
    Route::put('users/change-password', 'UserController@changePassword');
    Route::resource('users', 'UserController')->except(['create', 'edit']);

    // Category
    Route::get('categories/{category}/followers', 'CategoryController@followers');
    Route::post('categories/{category}/follow', 'CategoryController@follow');
    Route::delete('categories/{category}/follow', 'CategoryController@unfollow');
    Route::get('categories/{user}/articles', 'CategoryController@articles');
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::get('tags/{tag}/followers', 'TagController@followers');
    Route::post('tags/{tag}/follow', 'TagController@follow');
    Route::delete('tags/{tag}/follow', 'TagController@unfollow');
    Route::get('tags/{user}/articles', 'TagController@articles');
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::get('articles/popular', 'ArticleController@popular');
    Route::get('articles/featured', 'ArticleController@featured');
    Route::post('articles/{id}/bookmark', 'BookmarkController@store');
    Route::delete('articles/{id}/bookmark', 'BookmarkController@destroy');
    Route::resource('articles', 'ArticleController')->except(['create', 'edit']);


    // Role
    Route::resource('roles', 'RoleController')->except(['show', 'create', 'edit']);

    // Search
    Route::group(['prefix' => 'search'], function () {
        Route::get('article', 'SearchController@article');
    });

    // Clap
    Route::resource('claps', 'ClapController')
        ->except(['index', 'show', 'create', 'edit', 'update'])
        ->middleware('auth');

    // Comment
    Route::resource('comments', 'CommentController')->except(['index', 'show', 'create', 'edit']);

    // Images
    Route::post('upload', 'ImageController@upload');
    Route::post('editor-upload', 'ImageController@editorUpload');
    Route::get('gallery', 'ImageController@gallery');
    Route::get('editor-gallery', 'ImageController@editorGallery');
});
