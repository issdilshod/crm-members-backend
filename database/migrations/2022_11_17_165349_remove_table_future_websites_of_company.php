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
        Schema::dropIfExists('future_websites');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('future_websites', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('entity_uuid')->nullable();
            $table->string('domain')->nullable()->default('');
            $table->string('category')->nullable()->default('');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }
};
