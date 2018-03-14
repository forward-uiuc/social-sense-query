<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorizations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
						$table->string('access_token')->nullable(true);
						$table->string('refresh_token')->nullable(true);
						$table->string('provider');
						$table->json('meta')->nullable(true);

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
        Schema::dropIfExists('authorizations');
    }
}
