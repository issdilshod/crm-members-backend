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
            $table->string('legal_name')->default('')->nullable()->change();
            $table->string('sic_code_uuid')->default('')->nullable()->change();
            $table->string('director_uuid')->default('')->nullable()->change();
            $table->string('incorporation_state_uuid')->default('')->nullable()->change();
            $table->string('incorporation_state_name')->default('')->nullable()->change();
            $table->string('doing_business_in_state_uuid')->default('')->nullable()->change();
            $table->string('doing_business_in_state_name')->default('')->nullable()->change();
            $table->string('ein')->default('')->nullable()->change();
            $table->string('business_number')->default('')->nullable()->change();
            $table->string('business_number_type')->default('')->nullable()->change();
            $table->string('voip_provider')->default('')->nullable()->change();
            $table->string('voip_login')->default('')->nullable()->change();
            $table->string('voip_password')->default('')->nullable()->change();
            $table->string('business_mobile_number_provider')->default('')->nullable()->change();
            $table->string('business_mobile_number_login')->default('')->nullable()->change();
            $table->string('business_mobile_number_password')->default('')->nullable()->change();
            $table->string('website')->default('')->nullable()->change();
            $table->string('db_report_number')->default('')->nullable()->change();
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
            $table->string('legal_name')->default(null)->nullable(false)->change();
            $table->string('sic_code_uuid')->default(null)->nullable(false)->change();
            $table->string('director_uuid')->default(null)->nullable(false)->change();
            $table->string('incorporation_state_uuid')->default(null)->nullable(false)->change();
            $table->string('incorporation_state_name')->default(null)->nullable(false)->change();
            $table->string('doing_business_in_state_uuid')->default(null)->nullable(false)->change();
            $table->string('doing_business_in_state_name')->default(null)->nullable(false)->change();
            $table->string('ein')->default(null)->nullable(false)->change();
            $table->string('business_number')->default(null)->nullable(false)->change();
            $table->string('business_number_type')->default(null)->nullable(false)->change();
            $table->string('voip_provider')->default(null)->nullable(false)->change();
            $table->string('voip_login')->default(null)->nullable(false)->change();
            $table->string('voip_password')->default(null)->nullable(false)->change();
            $table->string('business_mobile_number_provider')->default(null)->nullable(false)->change();
            $table->string('business_mobile_number_login')->default(null)->nullable(false)->change();
            $table->string('business_mobile_number_password')->default(null)->nullable(false)->change();
            $table->string('website')->default(null)->nullable(false)->change();
            $table->string('db_report_number')->default(null)->nullable(false)->change();
        });
    }
};
