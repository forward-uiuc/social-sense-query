<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueryMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('query_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

						$table->integer('from_query_id')->unsigned();
						$table->foreign('from_query_id')->references('id')->on('queries')->onDelete('cascade');

						$table->integer('to_query_id')->unsigned();
						$table->foreign('to_query_id')->references('id')->on('queries')->onDelete('cascade');
						$table->json('mapping');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('query_mappings');
    }
}
