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

Route::model('post', 'App\Post');

Route::get('post/{post}', 'PostController@display');
