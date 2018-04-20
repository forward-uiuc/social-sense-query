<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDedicatedMetaQueryNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_query_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
						$table->integer('topology_id');

						$table->string('node_type');
						$table->integer('node_id')->unsigned();
						$table->boolean('resolved');

						$table->integer('stage_id')->unsigned();
						$table->foreign('stage_id')->references('id')->on('stages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meta_query_nodes');
    }
}
