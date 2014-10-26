<?php
class ApiController extends BaseController {

    public function post(){

        if(Input::has('api_key') && Input::has('content')){

            $api_key = Input::get('api_key');
            $content = Input::get('content');

            $settings = Settings::where('api_key', '=', $api_key)->first();

            $user_id = $settings->user_id;
            $default_networks = json_decode($settings->default_networks, true);

            $schedule = Carbon::now();

            if(!empty($default_networks)){

                $post = new Post;
                $post->user_id = $user_id;
                $post->content = $content;
                $post->date_time = $schedule;
                $post->save();
                $post_id = $post->id;

                foreach($default_networks as $network_id){
                    $post_network = new PostNetwork;
                    $post_network->user_id = $user_id;
                    $post_network->post_id = $post_id;
                    $post_network->network_id = $network_id;
                    $post_network->status = 1;
                    $post_network->save();
                }

                Queue::later($schedule, 'SendPost@fire', array('post_id' => $post_id));
                return 'success';
            }
        }

    }

}