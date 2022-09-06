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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('name')->nullable()->default('')->change();
            $table->string('website')->nullable()->default('')->change();
            $table->string('username')->nullable()->default('')->change();
            $table->string('password')->nullable()->default('')->change();
            $table->string('account_number')->nullable()->default('')->change();
            $table->string('routing_number')->nullable()->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('name')->nullable(false)->default(null)->change();
            $table->string('website')->nullable(false)->default(null)->change();
            $table->string('username')->nullable(false)->default(null)->change();
            $table->string('password')->nullable(false)->default(null)->change();
            $table->string('account_number')->nullable(false)->default(null)->change();
            $table->string('routing_number')->nullable(false)->default(null)->change();
        });
    }
};
