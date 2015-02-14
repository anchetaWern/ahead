<?php

class AdminController extends BaseController {

    protected $layout = 'layouts.admin';

    public function index(){

        $api_key = Settings::where('user_id', '=', Auth::user()->id)->pluck('api_key');
        $this->layout->title = 'Admin';
        $this->layout->content = View::make('admin.index', array('api_key' => $api_key));
    }


    public function networks(){

        $networks = Network::where('user_id', '=', Auth::user()->id)->get();

        $page_data = array(
            'network_count' => count($networks),
            'networks' => $networks
        );

        $this->layout->title = 'Networks';
        $this->layout->content = View::make('admin.networks', $page_data);

    }


    public function logout(){

        Session::flush();
        Auth::logout();
        return Redirect::to("/login")
          ->with('message', array('type' => 'success', 'text' => 'You have successfully logged out'));

    }

}
