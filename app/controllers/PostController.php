<?php

class PostController extends BaseController {

    protected $layout = 'layouts.admin';

    public function newPost(){

        $user_id = Auth::user()->id;

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $default_networks = json_decode($settings->default_networks);
        $schedules = Schedule::where('user_id', '=', $user_id)->get();

        $custom_schedule_checked = '';
        $default_schedule = $settings->schedule_id;


        $page_data = array(
            'networks' => $networks,
            'default_networks' => $default_networks,
            'default_schedule' => $default_schedule,
            'schedules' => $schedules,
            'custom_schedule_checked' => $custom_schedule_checked
        );
        $this->layout->title = 'Schedule New Post';
        $this->layout->new_post = true;
        $this->layout->content = View::make('admin.new_post', $page_data);
    }


    public function createPost(){

        $user_id = Auth::user()->id;

        $rules = array(
            'content' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            if(Input::has('ajax')){
                return array('type' => 'danger', 'messages' => $validator->messages());
            }else{
                return Redirect::to('/post/new')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $content = Input::get('content');
        $post_now = Input::get('post_now');
        $schedule_type = Input::get('schedule');
        if(empty($schedule_type)){
            $schedule_type = Setting::where('user_id', '=', $user_id)->pluck('schedule_id');
        }

        $schedule_id = Settings::where('user_id', '=', $user_id)->pluck('schedule_id');
        $current_datetime = Carbon::now();

        $schedule = Carbon::now();

        if($post_now == '0' && $schedule_type != 'custom'){

            $schedule_id = $schedule_type;

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
        }else{
            $schedule = Carbon::parse(Input::get('schedule_value'));
        }


        $networks = Input::get('network');
        if(empty($networks)){
            $networks = Setting::where('user_id', '=', $user_id)->pluck('default_networks');
            $networks = json_decode($networks, true);
        }

        $post = new Post;
        $post->user_id = $user_id;
        $post->content = $content;
        $post->date_time = $schedule->toDateTimeString();
        $post->save();
        $post_id = $post->id;


        foreach($networks as $network_id){
            $post_network = new PostNetwork;
            $post_network->user_id = $user_id;
            $post_network->post_id = $post_id;
            $post_network->network_id = $network_id;
            $post_network->status = 1;
            $post_network->save();
        }

        Queue::later($schedule, 'SendPost@fire', array('post_id' => $post_id));


        if(Input::has('ajax')){
            return array('type' => 'success', 'text' => 'Your post was scheduled! It will be published on ' . $schedule->format('l jS \o\f F \a\t h:i A'));
        }
        return Redirect::to('/post/new')
            ->with('message', array('type' => 'success', 'text' => 'Your post was scheduled! It will be published on ' . $schedule->format('l jS \o\f F \a\t h:i A')));
    }


    public function posts(){

        $user_id = Auth::user()->id;

        $posts = Post::where('user_id', '=', $user_id)
            ->orderBy('date_time', 'DESC')
            ->paginate(10);

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $default_networks = json_decode($settings->default_networks);
        $schedules = Schedule::where('user_id', '=', $user_id)->get();

        $custom_schedule_checked = '';
        $default_schedule = $settings->schedule_id;

        $page_data = array(
            'posts' => $posts,
            'post_count' => count($posts),
            'networks' => $networks,
            'default_networks' => $default_networks,
            'default_schedule' => $default_schedule,
            'schedules' => $schedules,
            'custom_schedule_checked' => $custom_schedule_checked
        );

        $this->layout->title = 'Posts';
        $this->layout->posts = true;
        $this->layout->posts_list = true;
        $this->layout->handlebars = true;
        $this->layout->content = View::make('admin.posts', $page_data);

    }


    public function postsCalendar(){

        $user_id = Auth::user()->id;

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $default_networks = json_decode($settings->default_networks);
        $schedules = Schedule::where('user_id', '=', $user_id)->get();

        $custom_schedule_checked = '';
        $default_schedule = $settings->schedule_id;


        $page_data = array(
            'networks' => $networks,
            'default_networks' => $default_networks,
            'default_schedule' => $default_schedule,
            'schedules' => $schedules,
            'custom_schedule_checked' => $custom_schedule_checked
        );

        $this->layout->title = 'Posts';
        $this->layout->handlebars = true;
        $this->layout->posts = true;
        $this->layout->posts_calendar = true;
        $this->layout->new_post = true;
        $this->layout->content = View::make('admin.posts_calendar', $page_data);

    }


    public function postsCalendarItems(){

        $start = Input::get('start');
        $end = Input::get('end');

        $start_date = date('Y-m-d', strtotime($start));
        $end_date = date('Y-m-d', strtotime($end));

        $posts = Post::where('user_id', '=', Auth::user()->id)
            ->select('id', DB::raw("SUBSTRING(content, 1, 25) AS title"), 'date_time AS start')
            ->whereRaw(DB::raw("DATE(date_time) BETWEEN '$start_date' AND '$end_date'"))
            ->get();

        $posts_items = array();
        foreach($posts as $p){

            $background_color = '#A9FF86';
            if($p->published == 1){
                $background_color = '#EBFFA3';
            }


            $posts_items[] = array(
                'id' => $p->id,
                'start' => $p->start,
                'end' => Carbon::parse($p->start)->addMinutes(15)->toDateTimeString(),
                'title' => $p->title,
                'borderColor' => '#ececec',
                'textColor' => '#000',
                'backgroundColor' => $background_color
            );
        }

        return $posts_items;

    }


    public function viewPost($post_id){

        $user_id = Auth::user()->id;

        $post = Post::where('user_id', '=', $user_id)
            ->where('id', '=', $post_id)
            ->first();

        $networks = Network::where('user_id', '=', $user_id)->get();
        $settings = Settings::where('user_id', '=', $user_id)->first();

        $selected_networks = PostNetwork::where('post_id', $post_id)
            ->where('status', '=', 1)
            ->lists('network_id');

        $response_data = array(
            'post_id' => $post_id,
            'post' => $post,
            'networks' => $networks,
            'selected_networks' => $selected_networks
        );

        return $response_data;

    }


    public function updatePost(){

        $user_id = Auth::user()->id;

        $rules = array(
            'content' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        $post_id = Input::get('id');

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

        $response_data = array(
            'type' => 'success',
            'text' => 'Post was updated!',
            'post' => array(
                'id' => $post_id,
                'title' => substr($content, 0, 24),
                'content' => $content
            )
        );

        return $response_data;
    }


    public function postNetworks(){

        $id = Input::get('id');
        $posts_networks = DB::table('posts_networks')
            ->join('networks', 'posts_networks.network_id', '=', 'networks.id')
            ->where('posts_networks.post_id', '=', $id)
            ->where('posts_networks.status', '=', 1)
            ->select('networks.network', 'networks.username')
            ->get();

        return $posts_networks;

    }


}