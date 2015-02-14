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
            if(Input::has('queue')){

                $schedule_id = $settings->schedule_id;
                $interval = Schedule::find($schedule_id);

                if($interval->rule == 'add'){

                   $schedule = $current_datetime->modify('+ ' . $interval->period);

                }else if($interval->rule == 'random'){

                    $current_day = date('d');

                    $from_datetime = Carbon::now();
                    $to_datetime = $from_datetime->copy()->modify('+ ' . $interval->period);
                    $days_to_add = $from_datetime->diffInDays($to_datetime);

                    $day = mt_rand($current_day, $current_day + $days_to_add);
                    $hour = mt_rand(1, 23);
                    $minute = mt_rand(0, 59);
                    $second = mt_rand(0, 59);

                    //year, month and timezone is null
                    $schedule = Carbon::create(null, null, $day, $hour, $minute, $second, null);
                }

                if(empty($schedule)){
                    $schedule = $current_datetime->addHours(1);
                }

            }



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
                $response_data = array(
                    'type' => 'success',
                    'text' => 'Your post was scheduled! It will be published on ' . $schedule->format('l jS \o\f F \a\t h:i A')
                );
                return $response_data;
            }
        }

    }

}