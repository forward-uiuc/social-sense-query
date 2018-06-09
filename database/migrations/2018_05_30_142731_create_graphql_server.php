<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGraphqlServer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graphql_servers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
			$table->string('name');
			$table->string('url');
			$table->string('slug');
			$table->string('description');
			$table->json('schema')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('graphql_servers');
    }
}
