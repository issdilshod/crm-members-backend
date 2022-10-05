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
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn('telegram_id');
        });

        Schema::table('telegram_users', function (Blueprint $table) {
            $table->bigInteger('telegram_id')->unique()->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn('telegram_id');
        });

        Schema::table('telegram_users', function (Blueprint $table) {
            $table->integer('telegram_id')->unique()->after('uuid');
        });
    }
};
