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
Route::get('admin/{item?}/{id?}', 'WelcomeController@admin');
Route::get('login', 'WelcomeController@login');
Route::post('login', 'UserController@authenticate');
Route::get('logout', 'UserController@logout');

Route::resource('api/v1/group', 'GroupController', ['except' => ['edit', 'create']]);
Route::resource('api/v1/user', 'UserController', ['except' => ['edit', 'create']]);
Route::resource('api/v1/post', 'PostController', ['except' => ['edit', 'create']]);
Route::resource('api/v1/question', 'QuestionController', ['except' => ['edit', 'create']]);
Route::resource('api/v1/quiz', 'QuizController', ['except' => ['edit', 'create']]);

Route::delete('api/v1/post', 'PostController@destroy');

Route::post('api/v1/auth/login', 'UserController@authenticate');
Route::post('api/v1/auth/user', 'UserController@updateProfile');

Route::post('api/v1/follow/{group}', 'GroupController@follow');
Route::delete('api/v1/follow/{group}', 'GroupController@unFollow');
Route::post('api/v1/attend/{post}/{token?}', 'PostController@attend');
Route::delete('api/v1/attend/{post}', 'PostController@unAttend');
Route::post('api/v1/like/{post}', 'PostController@like');
Route::delete('api/v1/like/{post}', 'PostController@unLike');
Route::post('api/v1/favorite/{post}', 'PostController@favorite');
Route::delete('api/v1/favorite/{post}', 'PostController@unFavorite');
Route::post('api/v1/post/{post}/attendee/{user}', 'PostController@attendeeApproval');

Route::model('post', 'App\Post');
Route::model('group', 'App\Group');
Route::model('user', 'App\User');
Route::model('question', 'App\Question');
Route::model('quiz', 'App\Quiz');

Route::get('post/{post}', 'PostController@display');
Route::get('quizzes/{section?}', 'WelcomeController@quiz');
Route::get('credit-policy', 'WelcomeController@creditPolicy');
