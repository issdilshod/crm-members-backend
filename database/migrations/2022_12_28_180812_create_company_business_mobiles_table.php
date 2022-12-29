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
        Schema::create('company_business_mobiles', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('entity_uuid')->nullable();

            $table->string('business_mobile_number')->nullable();
            $table->string('business_mobile_provider')->nullable();
            $table->string('business_mobile_website')->nullable();
            $table->string('business_mobile_login')->nullable();
            $table->string('business_mobile_password')->nullable();
            $table->string('card_on_file')->nullable();
            $table->string('card_last_four_digit')->nullable();
            $table->string('card_holder_name')->nullable();

            $table->string('parent')->nullable();

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
        Schema::dropIfExists('company_business_mobiles');
    }
};
