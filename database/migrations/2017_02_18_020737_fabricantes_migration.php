<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FabricantesMigration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('fabricantes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('nombre');
			$table->integer('telefono');
			$table->enum('tipo', array('completos', 'autopartes', 'semipartes' ));	
			$table->string('foto')->default('desconocida');
			$table->nullableTimestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('fabricantes');
	}

}
