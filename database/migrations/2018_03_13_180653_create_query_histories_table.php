<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('query_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

						$table->integer('duration')->unsigned(); // Duration in milliseconds
						$table->boolean('has_error');
						$table->json('data');

						$table->integer('query_id')->unsigned();
    				$table->foreign('query_id')->references('id')->on('queries')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('query_histories');
    }
}
