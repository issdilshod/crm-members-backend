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
        Schema::table('companies', function (Blueprint $table) {
            // remove old fields
            $table->dropColumn('phone_type');
            $table->dropColumn('phone_number');
            // new fields
            $table->string('business_number')->after('ein');
            $table->string('business_number_type')->after('business_number');
            $table->string('voip_provider')->after('business_number_type');
            $table->string('voip_login')->after('voip_provider');
            $table->string('voip_password')->after('voip_login');
            $table->string('business_mobile_number_provider')->after('voip_password');
            $table->string('business_mobile_number_login')->after('business_mobile_number_provider');
            $table->string('business_mobile_number_password')->after('business_mobile_number_login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('business_number');
            $table->dropColumn('business_number_type');
            $table->dropColumn('voip_provider');
            $table->dropColumn('voip_login');
            $table->dropColumn('voip_password');
            $table->dropColumn('business_mobile_number_provider');
            $table->dropColumn('business_mobile_number_login');
            $table->dropColumn('business_mobile_number_password');

            $table->string('phone_type');
            $table->string('phone_number');
        });
    }
};
