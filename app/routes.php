<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::filter('nocache', function($route, $request, $response){
  $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
  $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
  $response->header('Pragma', 'no-cache');
  return $response;
});

Route::pattern('id', '[0-9]+');

Route::get('/', 'HomeController@index');

Route::get('/login', 'HomeController@login');
Route::post('/login', 'HomeController@doLogin');

Route::get('/register', 'HomeController@register');
Route::post('/register', 'HomeController@doRegister');

Route::get('/logout', 'AdminController@logout');

Route::group(array('before' => 'auth', 'after' => 'nocache'), function(){

    Route::get('/admin', 'AdminController@index');

    Route::get('/networks', 'AdminController@networks');

    Route::get('/account', 'AccountController@account');
    Route::post('/account', 'AccountController@updateAccount');

    Route::get('/settings', 'SettingsController@settings');
    Route::post('/settings', 'SettingsController@updateSettings');

    Route::get('/schedules/new', 'SettingsController@newSchedule');
    Route::post('/schedules/create', 'SettingsController@createSchedule');

    Route::get('/schedules', 'SettingsController@schedules');


    Route::get('/post/new', 'PostController@newPost');
    Route::post('/post/create', 'PostController@createPost');

    Route::get('/posts', 'PostController@posts');
    Route::get('/posts/{id}/edit', 'PostController@editPost');
    Route::post('/posts', 'PostController@updatePost');
});

Route::get('/fb/redirect', 'SocialController@redirectFacebook');
Route::get('/fb/connect', 'SocialController@connectFacebook');

Route::get('/twitter/redirect', 'SocialController@redirectTwitter');
Route::get('/twitter/connect', 'SocialController@connectTwitter');

Route::get('/linkedin/redirect', 'SocialController@redirectLinkedin');
Route::get('/linkedin/connect', 'SocialController@connectLinkedin');

Route::get('/password/forgot', 'RemindersController@getRemind');
Route::post('/password/remind', 'RemindersController@postRemind');

Route::get('/password/reset/{token}', 'RemindersController@getReset');
Route::post('/password/reset', 'RemindersController@postReset');