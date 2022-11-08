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
        Schema::create('messages', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('chat_uuid')->nullable();
            $table->string('user_uuid')->nullable();
            $table->text('message')->nullable()->default('');
            $table->tinyInteger('status');
            $table->timestamps();

            $table->foreign('chat_uuid')->references('uuid')->on('chats');
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
        Schema::dropIfExists('messages');
    }
};
