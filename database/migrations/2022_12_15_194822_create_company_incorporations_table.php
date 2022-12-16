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
        Schema::create('company_incorporations', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('entity_uuid')->nullable();
            $table->string('annual_report')->nullable();
            $table->string('effective_date')->nullable();
            $table->string('registered_agent_exists')->nullable();
            $table->string('notes')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();

            $table->foreign('entity_uuid')->references('uuid')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_incorporations');
    }
};
