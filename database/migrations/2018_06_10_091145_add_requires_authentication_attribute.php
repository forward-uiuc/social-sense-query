<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequiresAuthenticationAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('graphql_servers', function (Blueprint $table) {
			$table->boolean('requires_authentication')->default(false);
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
			$table->dropColumn('requires_authentication');
        });
    }
}
