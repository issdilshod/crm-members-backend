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
        Schema::table('companies', function (Blueprint $table) {
            //
            $table->foreign('user_uuid', 'fk_company_to_users')->references('uuid')->on('users');
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            //
            $table->foreign('entity_uuid', 'fk_bank_account_to_companies')->references('uuid')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comapnies', function (Blueprint $table) {
            //
            $table->dropForeign(['fk_company_to_users']);
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            //
            $table->dropForeign(['fk_bank_account_to_companies']);
        });
    }
};
