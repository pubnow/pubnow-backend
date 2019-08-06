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
    Route::get('users/invite-requests', 'UserController@inviteRequests');
    Route::get('users/organizations', 'UserController@organizations');

    Route::post('users/{user}/follow', 'UserController@follow');
    Route::delete('users/{user}/follow', 'UserController@unfollow');

    Route::get('users/{user}/following-users', 'UserController@followingUsers');
    Route::get('users/{user}/following-organizations', 'UserController@followingOrganizations');
    Route::get('users/{user}/followers', 'UserController@followers');

    Route::get('users/{user}/following-categories', 'UserController@followingCategories');
    Route::get('users/{user}/following-tags', 'UserController@followingTags');

    Route::get('users/bookmarks', 'UserController@bookmarks');
    Route::get('users/{user}/articles', 'UserController@articles');
    Route::put('users/change-password', 'UserController@changePassword');

    Route::get('users/admin-members', 'UserController@adminMembers');
    Route::get('users/new-members', 'UserController@newMembers');
    Route::get('users/featured-authors', 'UserController@featuredAuthors');
    Route::get('users/active-members', 'UserController@activeMembers');

    Route::resource('users', 'UserController')->except(['create', 'edit']);

    // Category
    Route::get('categories/{category}/followers', 'CategoryController@followers');
    Route::post('categories/{category}/follow', 'CategoryController@follow');
    Route::delete('categories/{category}/follow', 'CategoryController@unfollow');
    Route::get('categories/{category}/articles', 'CategoryController@articles');
    Route::resource('categories', 'CategoryController')->except(['create', 'edit']);

    // Tag
    Route::get('tags/{tag}/followers', 'TagController@followers');
    Route::post('tags/{tag}/follow', 'TagController@follow');
    Route::delete('tags/{tag}/follow', 'TagController@unfollow');
    Route::get('tags/{tag}/articles', 'TagController@articles');
    Route::resource('tags', 'TagController')->except(['create', 'edit']);

    // Article
    Route::get('articles/{article}/comments', 'ArticleController@comments');
    Route::get('articles/popular', 'ArticleController@popular');
    Route::get('articles/featured', 'ArticleController@featured');
    Route::post('articles/{id}/bookmark', 'BookmarkController@store');
    Route::delete('articles/{id}/bookmark', 'BookmarkController@destroy');
    Route::post('articles/{article}/clap', 'ArticleController@clap');
    Route::delete('articles/{article}/clap', 'ArticleController@unclap');
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

    // Organization
    Route::post('organizations/{organization}/follow', 'OrganizationController@follow');
    Route::delete('organizations/{organization}/follow', 'OrganizationController@unfollow');

    Route::get('organizations/{organization}/followers', 'OrganizationController@followers');

    Route::get('organizations/{organization}/members', 'OrganizationController@members');
    Route::get('organizations/{organization}/statistic', 'OrganizationController@statistic');
    Route::get('organizations/{organization}/articles', 'OrganizationController@articles');
    Route::resource('organizations', 'OrganizationController')->except(['create', 'edit']);

    // Images
    Route::post('upload', 'ImageController@upload');
    Route::post('editor-upload', 'ImageController@editorUpload');
    Route::get('gallery', 'ImageController@gallery');

    // Series
    Route::resource('series', 'SeriesController');

    Route::get('editor-gallery', 'ImageController@editorGallery');

    // Invite request
    Route::post('invite-requests/{inviteRequest}/accept', 'InviteRequestController@accept');
    Route::post('invite-requests/{inviteRequest}/deny', 'InviteRequestController@deny');
    Route::resource('invite-requests', 'InviteRequestController')->except('create', 'edit', 'show');

    // feedback
    Route::resource('feedback', 'FeedbackController');

    // admin statistical
    Route::get('admin/statistical', 'AdminStatisticalController@statisticalUserRegister');
});
