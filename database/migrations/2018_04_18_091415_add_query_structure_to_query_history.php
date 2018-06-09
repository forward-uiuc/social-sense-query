<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQueryStructureToQueryHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_histories', function (Blueprint $table) {
			$table->json('query_structure')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('query_histories', function (Blueprint $table) {
					$table->dropColumn('query_structure');
        });
    }
}
