<?php

class AccountController extends BaseController {

    protected $layout = 'layouts.admin';

    public function account(){
        $this->layout->title = 'Account';
        $this->layout->content = View::make('admin.account');
    }

    public function updateAccount(){

        $rules = array(
            'email' => 'required|email',
            'password' => 'min:8'
        );

        $validator = Validator::make(Input::all(), $rules);
        if($validator->fails()){
            return Redirect::to('/account')
                ->withErrors($validator);
        }

        $email = Input::get('email');

        $user = User::find(Auth::user()->id);
        $user->email = $email;

        if(Input::has('password')){
            $user->password = Hash::make(Input::get('password'));
        }
        $user->save();
        return Redirect::to('/account')
            ->with('message', array('type' => 'success', 'text' => 'Your account was updated!'));

    }

}