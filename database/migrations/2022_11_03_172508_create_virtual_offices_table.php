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
        Schema::create('virtual_offices', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('user_uuid');
            $table->string('vo_provider_name')->nullable();
            
            // address
            $table->string('street_address')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal')->nullable();
            $table->string('country')->nullable();

            $table->string('vo_provider_domain')->nullable();
            $table->string('vo_provider_username')->nullable();
            $table->string('vo_provider_password')->nullable();

            $table->tinyInteger('status');
            $table->tinyInteger('approved');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('virtual_offices');
    }
};
