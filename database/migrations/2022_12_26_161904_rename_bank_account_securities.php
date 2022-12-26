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
        Schema::rename('bank_account_securities', 'account_securities');

        Schema::table('account_securities', function (Blueprint $table) {
            $table->dropForeign('bank_account_securities_entity_uuid_foreign');
            $table->string('entity_uuid')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('account_securities', 'bank_account_securities'); 

        Schema::table('bank_account_securities', function (Blueprint $table) {
            $table->foreign('entity_uuid')->references('uuid')->on('bank_accounts');
            $table->string('entity_uuid')->nullable(false)->change();
        });
    }
};
