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
        Schema::table('user_access_tokens', function (Blueprint $table) {
            $table->string('ip')->after('token')->nullable();
            $table->string('device')->after('ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_access_tokens', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('device');
        });
    }
};
