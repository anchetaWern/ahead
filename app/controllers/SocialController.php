<?php

class SocialController extends BaseController {


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

                    $user['inputs'] = array(
                        'username' => $screen_name,
                        'email' => '',
                        'type' => $network_type,
                        'social_id' => $twitter_id
                    );

                    Event::fire('user.create', array($user));
                }

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

                        $user['inputs'] = array(
                            'username' => $user_name,
                            'email' => '',
                            'type' => $network_type,
                            'social_id' => $linkedin_id
                        );

                        Event::fire('user.create', array($user));
                    }

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

            $id = $response_body['id'];
            $name = $response_body['name'];


            if(!Auth::check()){

                $user = User::where('social_id', '=', $id)->where('type', '=', 'facebook')->first();
                if(empty($user)){

                    $user['inputs'] = array(
                        'username' => $name,
                        'email' => '',
                        'type' => $network_type,
                        'social_id' => $id
                    );

                    Event::fire('user.create', array($user));

                }
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


}