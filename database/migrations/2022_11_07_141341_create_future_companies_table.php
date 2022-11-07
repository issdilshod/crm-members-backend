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
        Schema::create('future_companies', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('user_uuid');
            $table->string('sic_code_uuid')->nullable();
            $table->string('incorporation_state_uuid')->nullable();
            $table->string('doing_business_in_state_uuid')->nullable();
            $table->string('virtual_office_uuid')->nullable();
            $table->date('revival_date')->nullable();
            $table->float('revival_fee')->nullable();
            $table->tinyInteger('status');
            $table->tinyInteger('approved');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('sic_code_uuid')->references('uuid')->on('sic_codes');
            $table->foreign('incorporation_state_uuid')->references('uuid')->on('states');
            $table->foreign('doing_business_in_state_uuid')->references('uuid')->on('states');
            $table->foreign('virtual_office_uuid')->references('uuid')->on('virtual_offices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_companies');
    }
};
