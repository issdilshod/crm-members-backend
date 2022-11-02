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
        Schema::create('websites_futures', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('user_uuid');
            $table->string('sic_code_uuid')->nullable();
            $table->string('link')->nullable()->default('');
            $table->tinyInteger('status');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('sic_code_uuid')->references('uuid')->on('sic_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('websites_futures');
    }
};
