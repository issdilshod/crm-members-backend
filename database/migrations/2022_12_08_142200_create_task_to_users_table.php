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
        Schema::create('task_to_users', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('task_uuid')->nullable();
            $table->string('user_uuid')->nullable();
            $table->boolean('is_group');
            $table->tinyInteger('status');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('task_uuid')->references('uuid')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_to_users');
    }
};
