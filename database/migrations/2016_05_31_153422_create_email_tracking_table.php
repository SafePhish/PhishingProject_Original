<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTrackingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_tracking', function(Blueprint $table)
		{
			$table->increments('EML_Id');
			$table->string('EML_Ip');
			$table->string('EML_Host');
			$table->string('EML_Username');
			$table->string('EML_ProjectName');
			$table->date('EML_AccessDate');
			$table->time('EML_AccessTime');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_tracking');
	}

}
