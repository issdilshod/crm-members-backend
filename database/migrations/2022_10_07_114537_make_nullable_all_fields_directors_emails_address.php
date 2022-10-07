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
        Schema::table('directors', function (Blueprint $table) {
            $table->string('first_name')->default('')->nullable()->change();
            $table->string('middle_name')->default('')->nullable()->change();
            $table->string('last_name')->default('')->nullable()->change();
            $table->string('date_of_birth')->default('')->nullable()->change();
            $table->string('ssn_cpn')->default('')->nullable()->change();
            $table->string('company_association')->default('')->nullable()->change();
            $table->string('phone_type')->default('')->nullable()->change();
            $table->string('phone_number')->default('')->nullable()->change();
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->string('email')->default('')->nullable()->change();
            $table->string('password')->default('')->nullable()->change();
            $table->string('phone')->default('')->nullable()->change();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street_address')->default('')->nullable()->change();
            $table->string('address_line_2')->default('')->nullable()->change();
            $table->string('city')->default('')->nullable()->change();
            $table->string('state')->default('')->nullable()->change();
            $table->string('postal')->default('')->nullable()->change();
            $table->string('country')->default('')->nullable()->change();
            $table->string('address_parent')->default('')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('directors', function (Blueprint $table) {
            $table->string('first_name')->default(null)->nullable(false)->change();
            $table->string('middle_name')->default(null)->nullable(false)->change();
            $table->string('last_name')->default(null)->nullable(false)->change();
            $table->string('date_of_birth')->default(null)->nullable(false)->change();
            $table->string('ssn_cpn')->default(null)->nullable(false)->change();
            $table->string('company_association')->default(null)->nullable(false)->change();
            $table->string('phone_type')->default(null)->nullable(false)->change();
            $table->string('phone_number')->default(null)->nullable(false)->change();
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->string('email')->default(null)->nullable(false)->change();
            $table->string('password')->default(null)->nullable(false)->change();
            $table->string('phone')->default(null)->nullable(false)->change();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street_address')->default(null)->nullable(false)->change();
            $table->string('address_line_2')->default(null)->nullable(false)->change();
            $table->string('city')->default(null)->nullable(false)->change();
            $table->string('state')->default(null)->nullable(false)->change();
            $table->string('postal')->default(null)->nullable(false)->change();
            $table->string('country')->default(null)->nullable(false)->change();
            $table->string('address_parent')->default(null)->nullable(false)->change();
        });
    }
};
