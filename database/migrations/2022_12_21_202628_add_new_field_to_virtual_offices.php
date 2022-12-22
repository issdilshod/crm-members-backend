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
        Schema::table('virtual_offices', function (Blueprint $table) {
            $table->string('vo_signer_company_uuid')->after('vo_signer_uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('virtual_offices', function (Blueprint $table) {
            $table->dropColumn('vo_signer_company_uuid');
        });
    }
};
