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
    $settings_id = $settings->id;

    $schedule = new Schedule;
    $schedule->user_id = $user_id;
    $schedule->interval_id = 1;
    $schedule->name = 'Every 1 hour';
    $schedule->save();
    $schedule_id = $schedule->id;

    $settings = Settings::find($settings_id);
    $settings->schedule_id = $schedule_id;
    $settings->save();

    Auth::loginUsingId($user->id);

});