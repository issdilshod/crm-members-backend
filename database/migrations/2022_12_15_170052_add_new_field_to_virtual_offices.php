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
            $table->string('vo_signer_uuid')->after('user_uuid')->nullable();
            $table->string('contract_terms_notes')->after('contract_terms')->nullable();
            $table->date('contract_effective_date')->after('contract_terms_notes')->nullable();
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
            $table->dropColumn('vo_signer_uuid');
            $table->dropColumn('contract_terms_notes');
            $table->dropColumn('contract_effective_date');
        });
    }
};
