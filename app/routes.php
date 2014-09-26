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

    Route::get('/account', 'AdminController@account');
    Route::post('/account', 'AdminController@updateAccount');

    Route::get('/networks', 'AdminController@networks');

    Route::get('/twitter/redirect', 'AdminController@redirectTwitter');
    Route::get('/twitter/connect', 'AdminController@connectTwitter');

    Route::get('/linkedin/redirect', 'AdminController@redirectLinkedin');
    Route::get('/linkedin/connect', 'AdminController@connectLinkedin');

    Route::get('/fb/redirect', 'AdminController@redirectFacebook');
    Route::get('/fb/connect', 'AdminController@connectFacebook');

    Route::get('/post/new', 'AdminController@newPost');
    Route::post('/post/create', 'AdminController@createPost');

    Route::get('/posts', 'AdminController@posts');

    Route::get('/settings', 'AdminController@settings');
    Route::post('/settings', 'AdminController@updateSettings');

    Route::get('/schedules/new', 'AdminController@newSchedule');
    Route::post('/schedules/create', 'AdminController@createSchedule');
});

Route::get('/password/forgot', 'RemindersController@getRemind');
Route::post('/password/remind', 'RemindersController@postRemind');

Route::get('/password/reset/{token}', 'RemindersController@getReset');
Route::post('/password/reset', 'RemindersController@postReset');