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
});

Route::get('/password/forgot', 'RemindersController@getRemind');
Route::post('/password/remind', 'RemindersController@postRemind');

Route::get('/password/reset/{token}', 'RemindersController@getReset');
Route::post('/password/reset', 'RemindersController@postReset');

Route::get('/tester', function(){

    //$client = new GuzzleHttp\Client();

    $extended_accesstoken = 'CAAHpfXuL3vQBAH32HYIV9EcZC4BQz7fm4dWA1USfg9ZB1wX3kNKGr1tPsOgap2lV7EMXEh4fuP5CkGmgUmgg7SE91fErZBm6HfTZBAhJuXZB9EKRHndh3AHTNaozWe46AWrF2mEAkkBYWASrQVxI6OJyQKxUu5cwhtZCshGkzYkqZATwo4Eix36&expires=5183208';

    return preg_replace('/&expires\=[0-9]*/', '', $extended_accesstoken);

            //get pages
            $res = $client->get('https://graph.facebook.com/me/accounts', array(
                'query' => array(
                    'access_token' => $extended_accesstoken
                    )
                ));
            $response_body = $res->getBody();
            $pages = json_decode($response_body, true);

            if(!empty($pages['data'])){
                foreach($pages['data'] as $page){

                    if(in_array('CREATE_CONTENT', $page['perms'])){

                        $page_id = $page['id'];
                        $page_name = $page['name'];
                        $page_accesstoken = $page['access_token'];

                        $network = Network::where('user_id', '=', $user_id)
                            ->where('network', '=', $network_type)
                            ->where('network_id', '=', $page_id)
                            ->first();

                        if(!empty($network)){
                            $network->user_token = $page_accesstoken;
                            $network->save();
                        }else{
                            $network = new Network;
                            $network->user_id = $user_id;
                            $network->network = $network_type;
                            $network->user_token = $page_accesstoken;
                            $network->network_id = $page_id;
                            $network->username = $page_name;
                            $network->save();
                        }

                    }

                }
            }

});