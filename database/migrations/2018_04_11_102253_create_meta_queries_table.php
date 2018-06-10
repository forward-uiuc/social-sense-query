<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_queries', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->json('canvas');
			$table->json('topology');
			$table->string('schedule')->nullable(true);

			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('meta_queries');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
