<?php

class AdminController extends BaseController {

    protected $layout = 'layouts.admin';

    public function index(){

        $this->layout->title = 'Admin';
        $this->layout->content = View::make('admin.index');
    }


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

    public function networks(){

        $networks = Network::where('user_id', '=', Auth::user()->id)->get();

        $page_data = array(
            'network_count' => count($networks),
            'networks' => $networks
        );

        $this->layout->title = 'Networks';
        $this->layout->content = View::make('admin.networks', $page_data);

    }



    public function redirectTwitter(){

        $tokens = Twitter::oAuthRequestToken();
        Twitter::oAuthAuthenticate(array_get($tokens, 'oauth_token'));
        exit;

    }


    public function connectTwitter(){

        $token = Input::get('oauth_token');
        $verifier = Input::get('oauth_verifier');
        $accessToken = Twitter::oAuthAccessToken($token, $verifier);



        if(!empty($accessToken)){
            $user_token = $accessToken['oauth_token'];
            $user_secret = $accessToken['oauth_token_secret'];
            $twitter_id = $accessToken['user_id'];
            $screen_name = $accessToken['screen_name'];


            $network_type = 'twitter';

            if(!Auth::check()){

                $user = User::where('social_id', '=', $twitter_id)->where('type', '=', 'twitter')->first();
                if(empty($user)){
                    $user = new User;
                    $user->username = $screen_name;
                    $user->email = '';
                    $user->type = $network_type;
                    $user->social_id = $twitter_id;
                    $user->save();

                    $settings = new Settings;
                    $settings->user_id = $user->id;
                    $settings->default_networks = '[]';
                    $settings->save();
                }

                Auth::loginUsingId($user->id);
            }


            if(Auth::check()){
                $user_id = Auth::user()->id;

                $network = Network::where('user_id', '=', $user_id)
                    ->where('network', '=', $network_type)
                    ->where('network_id', '=', $twitter_id)
                    ->first();

                if(!empty($network)){

                    $network->user_token = $user_token;
                    $network->user_secret = $user_secret;
                    $network->save();

                }else{

                    $network = new Network;
                    $network->user_id = $user_id;
                    $network->network = $network_type;
                    $network->user_token = $user_token;
                    $network->user_secret = $user_secret;
                    $network->network_id = $twitter_id;
                    $network->username = $screen_name;
                    $network->save();
                }
            }

            return Redirect::to('/networks')
                ->with('message', array('type' => 'success', 'text' => 'You have successfully connected your Twitter account!'));
        }else{
            return Redirect::to('/networks')
                ->with('message', array('type' => 'danger', 'text' => 'Something went wrong while trying to connect to Twitter, please try again'));
        }

    }


    public function redirectLinkedin(){
        $provider = new Linkedin(Config::get('social.linkedin'));
        $provider->authorize();
    }


    public function connectLinkedIn(){

        if(Input::has('code')){

            $network_type = 'linkedin';

            $provider = new Linkedin(Config::get('social.linkedin'));

            try{

                $token = $provider->getAccessToken('authorizationCode', array('code' => Input::get('code')));
                $access_token = $token->accessToken;


                $resource = '/v1/people/~:(id,firstName,lastName)';
                $params = array(
                    'oauth2_access_token' => $access_token,
                    'format' => 'json'
                );

                $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
                $context = stream_context_create(array('http' => array('method' => 'GET')));
                $response = file_get_contents($url, false, $context);
                $user_data = json_decode($response, true);


                $linkedin_id = $user_data['id'];
                $user_name = $user_data['firstName'] . ' ' . $user_data['lastName'];

                if(!Auth::check()){

                    $user = User::where('social_id', '=', $linkedin_id)->where('type', '=', 'linkedin')->first();
                    if(empty($user)){
                        $user = new User;
                        $user->username = $user_name;
                        $user->email = '';
                        $user->type = $network_type;
                        $user->social_id = $linkedin_id;
                        $user->save();

                        $settings = new Settings;
                        $settings->user_id = $user->id;
                        $settings->default_networks = '[]';
                        $settings->save();
                    }

                    Auth::loginUsingId($user->id);

                }


                if(Auth::check()){

                    $user_id = Auth::user()->id;

                    $network = Network::where('user_id', '=', $user_id)
                        ->where('network', '=', $network_type)
                        ->where('network_id', '=', $linkedin_id)
                        ->first();

                    if(!empty($network)){

                        $network->user_token = $access_token;
                        $network->save();

                    }else{
                        $network = new Network;
                        $network->user_id = $user_id;
                        $network->network = $network_type;
                        $network->user_token = $access_token;
                        $network->user_secret = '';
                        $network->network_id = $linkedin_id;
                        $network->username = $user_name;
                        $network->save();
                    }


                    return Redirect::to('/networks')
                        ->with('message', array('type' => 'success', 'text' => 'You have successfully connected your Linkedin account!'));
                }


            }catch(Exception $e){

                return Redirect::to('/networks')
                    ->with('message', array('type' => 'danger', 'text' => 'An error occurred while trying to connect to LinkedIn, please try again'));
            }

        }else{
            return Redirect::to('/networks')
                ->with('message', array('type' => 'danger', 'text' => 'An error occurred while trying to connect to LinkedIn, please try again'));
        }

    }


    public function redirectFacebook(){
        $provider = new Facebook(Config::get('social.facebook'));
        if(!Input::has('code')){
            $provider->authorize();
        }
    }


    public function connectFacebook(){

        $provider = new Facebook(Config::get('social.facebook'));

        try{

            $network_type = 'facebook';

            $token = $provider->getAccessToken('authorizationCode', array('code' => Input::get('code')));
            $access_token = $token->accessToken;

            $client = new GuzzleHttp\Client();
            $res = $client->get('https://graph.facebook.com/me', array(
                    'query' =>  array(
                        'access_token' => $access_token
                    )
                )
            );

            $response_body = $res->getBody();
            $response_body = json_decode($response_body, true);

            $email = $response_body['email'];
            $id = $response_body['id'];
            $name = $response_body['name'];

            if(!Auth::check()){

                $user = User::where('social_id', '=', $id)->where('type', '=', 'facebook')->first();
                if(empty($user)){
                    $user = new User;
                    $user->username = $name;
                    $user->email = $email;
                    $user->type = $network_type;
                    $user->social_id = $id;
                    $user->save();

                    $settings = new Settings;
                    $settings->user_id = $user->id;
                    $settings->default_networks = '[]';
                    $settings->save();
                }

                Auth::loginUsingId($user->id);

            }


            if(Auth::check()){

                $res = $client->get('https://graph.facebook.com/oauth/access_token', array(
                    'query' =>  array(
                        'grant_type' => 'fb_exchange_token',
                        'client_id' => Config::get('social.facebook.clientId'),
                        'client_secret' => Config::get('social.facebook.clientSecret'),
                        'fb_exchange_token' => $access_token
                    )
                ));

                $extended_accesstoken_response_body = $res->getBody();
                $extended_accesstoken = str_replace('access_token=', '', $extended_accesstoken_response_body);
                $extended_accesstoken = preg_replace('/&expires\=[0-9]*/', '', $extended_accesstoken);

                $user_id = Auth::user()->id;

                $network = Network::where('user_id', '=', $user_id)
                    ->where('network', '=', $network_type)
                    ->where('network_id', '=', $id)
                    ->first();

                if(!empty($network)){
                    $network->user_token = $extended_accesstoken;
                    $network->save();
                }else{
                    $network = new Network;
                    $network->user_id = $user_id;
                    $network->network = $network_type;
                    $network->user_token = $extended_accesstoken;
                    $network->network_id = $id;
                    $network->username = $name;
                    $network->save();
                }

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


                return Redirect::to('/networks')
                    ->with('message', array('type' => 'success', 'text' => 'You have successfully connected your Facebook account!'));
            }


        }catch(Exception $e){

            return Redirect::to('/networks')
                ->with('message', array('type' => 'danger', 'text' => 'Something went wrong while trying to connect to your Facebook account. Please try again.'));
        }


    }


    public function newPost(){

        $user_id = Auth::user()->id;

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $default_networks = json_decode($settings->default_networks);
        $schedules = Schedule::where('user_id', '=', $user_id)->get();

        $page_data = array(
            'networks' => $networks,
            'default_networks' => $default_networks,
            'default_schedule' => $settings->schedule_id,
            'schedules' => $schedules
        );
        $this->layout->title = 'Schedule New Post';
        $this->layout->new_post = true;
        $this->layout->content = View::make('admin.new_post', $page_data);
    }


    public function createPost(){

        $user_id = Auth::user()->id;

        $rules = array(
            'content' => 'required',
            'schedule' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            return Redirect::to('/post/new')
                ->withErrors($validator);
        }

        $content = Input::get('content');
        $post_now = Input::get('post_now');

        $schedule_id = Settings::where('user_id', '=', $user_id)->pluck('schedule_id');
        $current_datetime = Carbon::now();

        $schedule = Carbon::now();

        if($post_now == '0'){
            if(!empty($schedule_id)){
                $interval_id = Schedule::where('user_id', '=', $user_id)->where('id', '=', $schedule_id)->pluck('interval_id');
                $interval = Interval::find($interval_id);

                if($interval->rule == 'add'){
                   $schedule = $current_datetime->addHours($interval->hours);
                }else if($interval->rule == 'random'){

                    $current_day = date('d');
                    $days_to_add = $interval->hours / 24;

                    $day = mt_rand($current_day, $current_day + $days_to_add);
                    $hour = mt_rand(1, 23);
                    $minute = mt_rand(0, 59);
                    $second = mt_rand(0, 59);

                    //year, month and timezone is null
                    $schedule = Carbon::create(null, null, $day, $hour, $minute, $second, null);
                }
            }

            $last_post = Post::where('user_id', '=', $user_id)
                ->orderBy('date_time', 'desc')
                ->first();
            if(!empty($last_post)){
                $new_datetime = Carbon::parse($last_post->date_time);
                if($interval->rule == 'add'){
                    $new_schedule = $new_datetime->addHours($interval->hours);
                    if($new_schedule->gt($schedule)){
                        $schedule = $new_schedule;
                    }
                }
            }

            if(empty($schedule)){
                $schedule = $current_datetime->addHours(1);
            }
        }

        if(Input::has('network')){

            $post = new Post;
            $post->user_id = $user_id;
            $post->content = $content;
            $post->date_time = $schedule;
            $post->save();
            $post_id = $post->id;

            $networks = Input::get('network');

            foreach($networks as $network_id){
                $post_network = new PostNetwork;
                $post_network->user_id = $user_id;
                $post_network->post_id = $post_id;
                $post_network->network_id = $network_id;
                $post_network->status = 1;
                $post_network->save();
            }

            Queue::later($schedule, 'SendPost@fire', array('post_id' => $post_id));
        }

        return Redirect::to('/post/new')
            ->with('message', array('type' => 'success', 'text' => 'Your post was scheduled!'));
    }


    public function posts(){

        $posts = Post::where('user_id', '=', Auth::user()->id)
            ->orderBy('date_time', 'DESC')
            ->paginate(10);

        $page_data = array(
            'posts' => $posts,
            'post_count' => count($posts)
        );

        $this->layout->title = 'Posts';
        $this->layout->content = View::make('admin.posts', $page_data);

    }


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
            'schedules' => $schedules
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

        $intervals = Interval::get();

        $page_data = array(
            'intervals' => $intervals
        );

        $this->layout->title = 'New Schedule';
        $this->layout->content = View::make('admin.new_schedule', $page_data);

    }


    public function createSchedule(){

        $rules = array(
            'name' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            return Redirect::to('/schedules/new')
                ->withErrors($validator);
        }

        $schedule = new Schedule;
        $schedule->user_id = Auth::user()->id;
        $schedule->interval_id = Input::get('interval');
        $schedule->name = Input::get('name');
        $schedule->save();

        return Redirect::to('/schedules/new')
            ->with('message', array('type' => 'success', 'text' => 'Schedule was created!'));
    }


    public function schedules(){

        $schedules = DB::table('schedules')
            ->join('intervals', 'schedules.interval_id', '=', 'intervals.id')
            ->select('schedules.name AS schedule_name', 'intervals.name AS interval_name')
            ->where('schedules.user_id', '=', Auth::user()->id)
            ->paginate(10);

        $page_data = array(
            'schedules' => $schedules
        );

        $this->layout->title = 'Schedules';
        $this->layout->content = View::make('admin.schedules', $page_data);

    }


    public function editPost($post_id){

        $user_id = Auth::user()->id;

        $post = Post::where('user_id', '=', $user_id)
            ->where('id', '=', $post_id)
            ->first();

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $selected_networks = PostNetwork::where('post_id', $post_id)
            ->where('status', '=', 1)
            ->lists('network_id');

        $page_data = array(
            'post_id' => $post_id,
            'post' => $post,
            'networks' => $networks,
            'selected_networks' => $selected_networks
        );

        $this->layout->title = 'Edit Post';
        $this->layout->content = View::make('admin.edit_post', $page_data);
    }


    public function updatePost(){

        $user_id = Auth::user()->id;

        $rules = array(
            'content' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        $post_id = Input::get('post_id');

        if($validator->fails()){
            return Redirect::back()
                ->withErrors($validator);
        }

        if(Input::has('network')){

            $content = Input::get('content');

            $post = Post::where('user_id', '=', $user_id)
                ->where('id', '=', $post_id)
                ->first();
            $post->content = $content;
            $post->save();

            $networks = Input::get('network');

            $post_networks = PostNetwork::where('post_id', '=', $post_id)
                ->lists('network_id');

            foreach($post_networks as $network_id){
                if(!in_array($network_id, $networks)){
                    $post_network = PostNetwork::where('network_id', '=', $network_id)
                        ->where('post_id', '=', $post_id)
                        ->first();
                    if(!empty($post_network)){
                        $post_network->status = 0;
                        $post_network->save();
                    }
                }
            }

            foreach($networks as $network_id){
                $post_network = PostNetwork::where('network_id', '=', $network_id)
                    ->where('post_id', '=', $post_id)
                    ->first();
                if(!empty($post_network)){
                    $post_network->status = 1;
                    $post_network->save();
                }else{
                    $post_network = new PostNetwork;
                    $post_network->user_id = $user_id;
                    $post_network->post_id = $post_id;
                    $post_network->network_id = $network_id;
                    $post_network->status = 1;
                    $post_network->save();
                }
            }
        }

        return Redirect::back()
            ->with('message', array('type' => 'success', 'text' => 'Post was updated!'));

    }


    public function logout(){

        Session::flush();
        Auth::logout();
        return Redirect::to("/login")
          ->with('message', array('type' => 'success', 'text' => 'You have successfully logged out'));

    }

}
