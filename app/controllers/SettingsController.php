<?php

class SettingsController extends BaseController {


    protected $layout = 'layouts.admin';

    public function settings(){

        $user_id = Auth::user()->id;

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $schedules = Schedule::where('user_id', '=', $user_id)->get();

        $default_networks = json_decode($settings->default_networks);

        $page_data = array(
            'networks' => $networks,
            'default_networks' => $default_networks,
            'default_schedule' => $settings->schedule_id,
            'schedules' => $schedules,
            'api_key' => $settings->api_key
        );

        $this->layout->title = 'Settings';
        $this->layout->content = View::make('admin.settings', $page_data);

    }


    public function updateSettings(){

        $current_settings = Input::get('settings');

        $settings = Settings::where('user_id', '=', Auth::user()->id)->first();
        $settings->default_networks = json_encode($current_settings);
        $settings->schedule_id = Input::get('schedule');
        $settings->save();

        return Redirect::to('/settings')
            ->with('message', array('type' => 'success', 'text' => 'Settings was updated!'));

    }


    public function newSchedule(){

        $page_data = array(
            'rules' => array('add', 'random')
        );

        $this->layout->title = 'New Schedule';
        $this->layout->content = View::make('admin.new_schedule', $page_data);

    }


    public function createSchedule(){

        $rules = array(
            'name' => 'required',
            'rule' => 'required',
            'period' => 'required'
        );

        $period = Input::get('period');

        try{
            Carbon::now()->modify('+ ' . $period);
        }catch(Exception $e){
            return Redirect::to('/schedules/new')
                ->with('message', array('type' => 'danger', 'text' => 'Period has invalid format'));
        }


        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            return Redirect::to('/schedules/new')
                ->withErrors($validator);
        }

        $schedule = new Schedule;
        $schedule->user_id = Auth::user()->id;
        $schedule->rule = Input::get('rule');
        $schedule->period = $period;
        $schedule->name = Input::get('name');
        $schedule->save();

        return Redirect::to('/schedules/new')
            ->with('message', array('type' => 'success', 'text' => 'Schedule was created!'));
    }


    public function schedules(){

        $schedules = Schedule::where('user_id', '=', Auth::user()->id)
            ->paginate(10);

        $page_data = array(
            'schedules' => $schedules
        );

        $this->layout->title = 'Schedules';
        $this->layout->content = View::make('admin.schedules', $page_data);

    }


}