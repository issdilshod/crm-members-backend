<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('directors', function (Blueprint $table) {
            $table->tinyInteger('approved')->after('status');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->tinyInteger('approved')->after('status');
        });

        Schema::table('websites_futures', function (Blueprint $table) {
            $table->tinyInteger('approved')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('directors', function (Blueprint $table) {
            $table->dropColumn('approved');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('approved');
        });

        Schema::table('websites_futures', function (Blueprint $table) {
            $table->dropColumn('approved');
        });
    }
};
