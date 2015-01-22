[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=wernancheta&url=https://github.com/anchetaWern/ahead&title=ahead&language=php&tags=github&category=software)


#Ahead

Allows you to schedule posts to your Facebook, Twitter and LinkedIn accounts.

##Demo

You can check out the demo [here](http://ec2-54-68-251-216.us-west-2.compute.amazonaws.com/)

##How to Install

###Prerequisite

- [Composer](https://getcomposer.org/) - it doesn't have to be installed in the server, though its recommended. If for some reason you can't have composer on the server. Just install it on your computer. Composer is used to install the dependencies of the project.
- SSH access
- Ability to install software on the server - this app depends on [beanstalkd](http://kr.github.io/beanstalkd/download.html), a queue server.

You can install Ahead by clicking on the 'Download Zip' button or using git clone:

```
git clone git@github.com:anchetaWern/ahead.git
```

Next use composer to install all the dependencies of the app. The app depends on the following libraries in order to function:

- [Carbon](https://github.com/briannesbitt/Carbon)
- [PHP League Oauth Client](https://github.com/thephpleague/oauth2-client)
- [PhiloNL Laravel Twitter](https://github.com/PhiloNL/Laravel-Twitter)
- [Pheanstalk](https://github.com/pda/pheanstalk)

You can install all the dependencies by navigating to the root of the project directory (where the `composer.json` file is) and then execute `composer install`. This may take a while depending on your internet connection.

###App Configuration

Once all the dependencies are installed, configure the project based on your server configuration. Here are some of the files that you need to edit:

- `app/config/database.php` - database configuration
- `app/config/app.php` - global application configuration
- `app/config/queue.php` - queue configuration
- `app/config/social.php` - facebook and linkedin configuration
- `app/config/packages/philo/twitter/config.php` - twitter configuration

For the database configuration (`database.php`), this app is using the mysql database. So you should edit the array item which says 'mysql'. You don't need to worry about everything, all you need to update is the username and the password. Yes the one's in caps, but you don't need to capitalize anything when you update these entries.

```
        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'ahead',
            'username'  => 'ROOT',
            'password'  => 'SECRET',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
```

Next is the app configuration (`app.php`). First you need to configure the timezone. This doesn't have to be the same as the timezone in which the server is located. It just have to be the same as the timezone in which most of your users are located. If you plan to use this app for personal use, then you can simply use your timezone. In my case its the following:

```
'timezone' => 'Asia/Manila',
```

Next, under your `providers`, add the following:

```
'Philo\Twitter\TwitterServiceProvider',
```

And under the `aliases`:

```
'Carbon'          => 'Carbon\Carbon',
'Twitter'         => 'Philo\Twitter\Facades\Twitter',
'Linkedin'        => 'League\OAuth2\Client\Provider\LinkedIn',
'Facebook'        => 'League\OAuth2\Client\Provider\Facebook',
```

Next configure the queue (`queue.php`). This project uses beanstalk, so add the configuration under beanstalk. The default one would look something like this:

```
        'beanstalkd' => array(
            'driver' => 'beanstalkd',
            'host'   => 'localhost',
            'queue'  => 'default',
            'ttr'    => 60,
        ),
```

Next is the configuration for linkedin and facebook (`social.php`). You would need to create your own app on [developer.facebook.com](https://developers.facebook.com/) for facebook and [developer.linkedin.com](https://developer.linkedin.com/) for linkedin to get the values for the `clientId` and `clientSecret`. If you're planning to have other users used this app, you also need to submit your facebook app for a review on facebook. As the app publishes posts on the users behalf. Facebook needs to review these kinds of app for the security of their users:

```
<?php
return array(
    'linkedin' => array(
        'clientId'  =>  '',
        'clientSecret'  =>  '',
        'redirectUri'   =>  url('/linkedin/connect'),
        'scopes' => 'r_basicprofile rw_nus',
    ),
    'facebook' => array(
        'clientId'  =>  '',
        'clientSecret'  =>  '',
        'redirectUri'   =>  url('/fb/connect'),
        'scopes' => 'publish_actions email user_groups manage_pages',
    ),
);
```

Lastly, the twitter configuration (`packages/philo/twitter/config.php`). You also need to create an app on [dev.twitter.com](https://dev.twitter.com/) to acquire the consumer key and consumer secret:

```
<?php

return array(
    'CONSUMER_KEY'    => '',
    'CONSUMER_SECRET' => ''
);
```

Next upload everything into your server except the `.git` folder.

Next login to your server using ssh. If you're using an Amazon EC2 instance, the command looks something like this:

```
ssh -i amazon-key.pem ubuntu@your-server.com
```

Once logged in, install beanstalkd. If you're on ubuntu it would look something like this:

```
sudo apt-get update
sudo apt-get install beanstalkd
```

Next, start it:

```
sudo service beanstalkd start
```

In order to continually run Laravel's queue, we also need to install [supervisor](http://supervisord.org/), you can do that by executing the following command:

```
sudo apt-get install supervisor
```

Next create a configuration file for our queue:

```
sudo nano /etc/supervisor/conf.d/ahead.conf
```

Next create a log file that will be used for logging errors or anything that happens while supervisor runs the queue:

```
touch /home/ubuntu/www/app/storage/logs/ahead_supervisord.log
```

Then paste in the following:

```
[program:ahead]
command=php artisan queue:listen --env=your_environment
directory=/home/ubuntu/www
stdout_logfile=/home/ubuntu/www/app/storage/logs/ahead_supervisord.log
redirect_stderr=true
```

Be sure to replace the value for the `directory` and the `stdout_logfile`.

Save the file by pressing `ctrl + x` and inputting `y` when asked to save.

Next let supervisor know of the configuration file you just created

```
sudo supervisorctl
reread
add ahead
start ahead
```

Now supervisor is running the queue and the app can just push things to it.

##Database

Still on ssh, navigate to the `public_html` directory and run the migration and seeder:

```
php artisan migrate
php artisan db:seed
```

Just input `yes` if being asked to confirm.

With that, you're now ready to deploy the app. If you have any questions or you're having issues installing the app. Just file an issue to this repository and I'll try to answer.

##TODO

- recurring posts
- calendar UI: different color for published and to be published post
- calendar: use modal when scheduling new post
- calendar:
- list UI: only show posts to be published
- delicious integration - pick URL's to be published
- profiles - a way of grouping connected accounts
