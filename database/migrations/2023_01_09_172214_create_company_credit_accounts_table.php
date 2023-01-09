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
        Schema::create('company_credit_accounts', function (Blueprint $table) {
            $table->string('uuid')->primary();

            $table->string('entity_uuid')->nullable();

            $table->string('is_active')->nullable();
            $table->string('name')->nullable();
            $table->string('website')->nullable();

            $table->text('phones')->nullable();

            $table->string('username')->nullable();
            $table->string('password')->nullable();

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
        Schema::dropIfExists('company_credit_accounts');
    }
};
