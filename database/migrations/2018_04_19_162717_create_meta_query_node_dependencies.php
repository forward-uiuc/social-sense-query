<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaQueryNodeDependencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_query_node_dependencies', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

			$table->integer('meta_query_node_output_id')->unsigned();
			$table->foreign('meta_query_node_output_id')->references('id')->on('meta_query_node_outputs')->onDelete('cascade');

			$table->integer('meta_query_node_input_id')->unsigned();
			$table->foreign('meta_query_node_input_id')->references('id')->on('meta_query_node_inputs')->onDelete('cascade');

			$table->integer('meta_query_node_id')->unsigned();
			$table->foreign('meta_query_node_id')->references('id')->on('meta_query_nodes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meta_query_node_dependencies');
    }
}
