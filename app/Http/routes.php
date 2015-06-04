<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::resource('api/v1/group', 'GroupController', ['except' => ['update', 'edit']]);
Route::resource('api/v1/user', 'UserController', ['except' => ['update', 'edit']]);
Route::resource('api/v1/post', 'PostController', ['except' => ['update', 'edit']]);

Route::post('api/v1/auth/login', 'UserController@authenticate');
Route::post('api/v1/auth/user', 'UserController@updateProfile');

Route::post('api/v1/follow/{group}', 'GroupController@follow');
Route::delete('api/v1/follow/{group}', 'GroupController@unFollow');
Route::post('api/v1/attend/{post}', 'PostController@attend');
Route::delete('api/v1/attend/{post}', 'PostController@unAttend');
Route::post('api/v1/like/{post}', 'PostController@like');
Route::delete('api/v1/like/{post}', 'PostController@unLike');

Route::model('post', 'App\Post');
Route::model('group', 'App\Group');
Route::model('user', 'App\User');

Route::get('post/{post}', 'PostController@display');
