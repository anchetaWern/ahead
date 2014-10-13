<?php
class SendPost {

    public function fire($job, $data)
    {

        $post_id = $data['post_id'];
        $post = Post::find($post_id);

        if(!empty($post)){

            $user_id = $post->user_id;

            $post_url = '';
            preg_match_all('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $post->content, $url_matches);

            if(!empty($url_matches)){

                $all_urls = $url_matches[0];
                $post_url = (!empty($all_urls[0])) ? $all_urls[0] : '';

            }

            $network_ids = PostNetwork::where('post_id', '=', $post_id)->lists('network_id');

            $network = Network::where('user_id', '=', $user_id)
                ->select('user_token', 'user_secret', 'network')
                ->whereIn('id', $network_ids)
                ->get();

            $client = new GuzzleHttp\Client();

            if(!empty($network)){

                foreach($network as $s){

                    if($s->network == 'twitter'){

                        try{

                            Twitter::setOAuthToken($s->user_token);
                            Twitter::setOAuthTokenSecret($s->user_secret);
                            $twitter_response = Twitter::statusesUpdate($post->content);


                        }catch(Exception $e){


                        }

                    }else if($s->network == 'linkedin'){

                        if(!empty($post_url)){

                            try{

                                $post_data = array(
                                    'comment' => $post->content,
                                    'content' => array(
                                        'description' => $post->content
                                    ),
                                    'visibility' => array('code' => 'anyone')
                                );

                                $post_data['content']['submittedUrl'] = $post_url;

                                $request_body = $post_data;

                                $linkedin_resource = '/v1/people/~/shares';
                                $request_format = 'json';


                                $linkedin_params = array(
                                    'oauth2_access_token' => $s->user_token,
                                    'format'  => $request_format,
                                );


                                $linkedinurl_info = parse_url('https://api.linkedin.com' . $linkedin_resource);

                                if(isset($linkedinurl_info['query'])){
                                    $query = parse_str($linkedinurl_info['query']);
                                    $linkedin_params = array_merge($linkedin_params, $query);
                                }

                                $request_url = 'https://api.linkedin.com' . $linkedinurl_info['path'] . '?' . http_build_query($linkedin_params);


                                $request_body = json_encode($request_body);
                                $linkedin_response = CurlRequester::requestCURL('POST', $request_url, $request_body, $request_format);


                            }catch(Exception $e){


                            }
                        }



                    }else if($s->network == 'facebook'){

                        try{

                            $post_data = array(
                                'access_token' => $s->user_token,
                                'message' => $post->content
                            );

                            if(!empty($post_url)){
                                $post_data['link'] = $post_url;
                            }

                            $res = $client->post('https://graph.facebook.com/me/feed', array(
                                'query' => $post_data
                            ));

                            $response_body = $res->getBody();
                            $response_body = json_decode($response_body, true);


                        }catch(Exception $e){


                        }

                    }
                }
            }
        }

        $post->published = 1;
        $post->save();

        $job->delete();

    }
}