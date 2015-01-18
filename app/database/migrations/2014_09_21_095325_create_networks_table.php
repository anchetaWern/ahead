<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('networks', function($table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('network');
            $table->string('network_id');
            $table->string('username');
            $table->string('user_token');
            $table->string('user_secret');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('networks');
	}

}
