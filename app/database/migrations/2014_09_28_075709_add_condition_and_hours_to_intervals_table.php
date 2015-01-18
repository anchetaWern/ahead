<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConditionAndHoursToIntervalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('intervals', function($table)
        {
            $table->string('rule');
            $table->integer('hours');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('intervals', function($table){
            $table->dropColumn('rule');
            $table->dropColumn('hours');
        });
	}

}
