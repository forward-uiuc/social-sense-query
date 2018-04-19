<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeQueryHistoryPolymorphic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('query_histories', function (Blueprint $table) {
					$table->dropForeign('query_id');
					$table->string('query_type')->default('App\Query');
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
    				$table->foreign('query_id')->references('id')->on('queries')->onDelete('cascade');
						$table->dropColumn(['query_id']);
        });
    }
}
