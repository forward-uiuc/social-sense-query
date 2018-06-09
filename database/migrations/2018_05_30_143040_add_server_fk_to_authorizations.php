<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServerFkToAuthorizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authorizations', function (Blueprint $table) {
			$table->integer('server_id')->unsigned();
			$table->foreign('server_id')->references('id')->on('graphql_servers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authorizations', function (Blueprint $table) {
			$table->dropForeign(['server_id']);
        	$table->dropColumn('server_id');
        });
    }
}
