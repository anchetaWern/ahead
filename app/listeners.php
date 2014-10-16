<?php
Event::listen('user.create', function($param){

    $user_data = $param['inputs'];

    $user = new User;
    $user->username = $user_data['username'];
    $user->email = $user_data['email'];
    if(!empty($user_data['password'])){
        $user->password = Hash::make($user_data['password']);
    }
    $user->type = $user_data['type'];
    $user->social_id = $user_data['social_id'];
    $user->save();
    $user_id = $user->id;

    $settings = new Settings;
    $settings->user_id = $user_id;
    $settings->default_networks = '[]';
    $settings->save();

    Auth::loginUsingId($user->id);

});