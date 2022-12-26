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
        Schema::create('contacts', function (Blueprint $table) {
            $table->string('uuid')->primary();

            $table->string('user_uuid')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_phone_number')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('online_account')->nullable();
            $table->string('account_username')->nullable();
            $table->string('account_password')->nullable();

            // security
            $table->string('security_questions')->nullable();
            $table->string('security_question1')->nullable();
            $table->string('security_question2')->nullable();
            $table->string('security_question3')->nullable();

            $table->string('notes')->nullable();

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
        Schema::dropIfExists('contacts');
    }
};
