<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFirmsTable extends Migration {

	public function up()
	{
		Schema::create('firms', function(Blueprint $table) {
			$table->increments('id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
			$table->softDeletes();
			$table->string('gstin');
			$table->string('machine_id');
			$table->datetime('start_date');
			$table->datetime('end_date');
			$table->enum('licence_type', array('TRIAL', 'PAID'));
		});
	}

	public function down()
	{
		Schema::drop('firms');
	}
}
