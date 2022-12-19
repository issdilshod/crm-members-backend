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
        Schema::table('company_incorporations', function (Blueprint $table) {
            $table->date('annual_report')->change();
            $table->renameColumn('annual_report', 'incorporation_date');
            $table->renameColumn('effective_date', 'annual_report_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_incorporations', function (Blueprint $table) {
            $table->string('annual_report')->change();
            $table->renameColumn('incorporation_date', 'annual_report');
            $table->renameColumn('annual_report_date', 'effective_date');
        });
    }
};
