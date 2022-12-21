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
        Schema::table('virtual_offices', function (Blueprint $table) {
            $table->string('online_email')->after('online_account')->nullable();

            $table->dropColumn('vo_provider_username');
            $table->dropColumn('vo_provider_password');

            $table->string('vo_provider_phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('virtual_offices', function (Blueprint $table) {
            $table->dropColumn('vo_provider_phone_number');
            $table->dropColumn('online_email');
            
            $table->string('vo_provider_username')->after('vo_provider_name')->nullable();
            $table->string('vo_provider_password')->after('vo_provider_username')->nullable();
        });
    }
};
