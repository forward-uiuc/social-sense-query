<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequiresAuthorizationToGraphqlServers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('graphql_servers', function (Blueprint $table) {
			$table->boolean('requires_authorization')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('graphql_servers', function (Blueprint $table) {
			$table->dropColumn('requires_authorization');
        });
    }
}
