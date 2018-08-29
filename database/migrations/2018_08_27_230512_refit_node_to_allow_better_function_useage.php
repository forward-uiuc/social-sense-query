<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefitNodeToAllowBetterFunctionUseage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meta_query_nodes', function (Blueprint $table) {
			$table->json('node_state')->nullable(true);
			$table->string('node_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meta_query_nodes', function (Blueprint $table) {
			$table->dropColumn('node_state');
			$table->integer('node_id')->unsigned()->change();
        });
    }
}
