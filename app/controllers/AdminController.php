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

        $user_id = Auth::user()->id;

        if(!empty($accessToken)){
            $user_token = $accessToken['oauth_token'];
            $user_secret = $accessToken['oauth_token_secret'];
            $twitter_id = $accessToken['user_id'];
            $screen_name = $accessToken['screen_name'];

            $network_type = 'twitter';

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

            $user_id = Auth::user()->id;
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

            $user_id = Auth::user()->id;

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

            $id = $response_body['id'];
            $name = $response_body['name'];


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

            $network_type = 'facebook';

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


        }catch(Exception $e){

            return Redirect::to('/networks')
                ->with('message', array('type' => 'danger', 'text' => 'Something went wrong while trying to connect to your Facebook account. Please try again.'));
        }


    }


    public function newPost(){

        $networks = Network::where('user_id', '=', Auth::user()->id)->get();

        $page_data = array(
            'networks' => $networks
        );
        $this->layout->title = 'Schedule New Post';
        $this->layout->content = View::make('admin.new_post', $page_data);
    }


    public function createPost(){

        $user_id = Auth::user()->id;

        $rules = array(
            'content' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            return Redirect::to('/post/new')
                ->withErrors($validator);
        }

        $content = Input::get('content');

        $schedule = Carbon::now()->addHours(1);
        $last_post = Post::where('user_id', '=', $user_id)
            ->orderBy('date_time', 'desc')
            ->first();
        if(!empty($last_post)){

            $dt = Carbon::parse($last_post->date_time);
            $schedule = $dt->addHours(1);
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


    public function logout(){

        Session::flush();
        Auth::logout();
        return Redirect::to("/login")
          ->with('message', array('type' => 'success', 'text' => 'You have successfully logged out'));

    }

}
