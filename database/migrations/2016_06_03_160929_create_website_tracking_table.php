<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsiteTrackingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('website_tracking', function(Blueprint $table)
		{
			$table->increments('WBS_Id');
			$table->string('WBS_Ip');
			$table->string('WBS_Host');
			$table->string('WBS_BrowserAgent');
			$table->string('WBS_ReqPath');
			$table->string('WBS_Username');
			$table->string('WBS_ProjectName');
			$table->date('WBS_AccessDate');
			$table->time('WBS_AccessTime');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('website_tracking');
	}

}
