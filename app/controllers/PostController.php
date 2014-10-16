<?php

class PostController extends BaseController {

    protected $layout = 'layouts.admin';

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



}