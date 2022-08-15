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
        Schema::create('companies', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('user_uuid');
            $table->string('legal_name');
            $table->string('sic_code_uuid');
            $table->string('director_uuid');
            $table->string('incorporation_state_uuid');
            $table->string('incorporation_state_name');
            $table->string('doing_business_in_state_uuid');
            $table->string('doing_business_in_state_name');
            $table->string('ein');
            $table->string('phone_type');
            $table->string('phone_number');
            $table->string('website');
            $table->string('db_report_number');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
