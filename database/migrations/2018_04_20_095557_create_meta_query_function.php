<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaQueryFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_query_functions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
			$table->json('inputs'); // The paths of inputs to this function and it's type, ex: value:Int
			$table->json('outputs'); // The paths of the outputs, ex: sum:Int
			$table->string('name'); // The name of this function, ex: 'sum'
			$table->json('state')->nullable(); // The state of this function
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meta_query_functions');
    }
}
