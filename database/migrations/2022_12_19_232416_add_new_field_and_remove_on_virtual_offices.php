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
            $table->dropColumn('deposit');
            $table->dropColumn('registration_fee');
            $table->dropColumn('etc');

            $table->string('contract')->after('agreement_terms')->nullable();

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
            $table->string('deposit')->afer('contract')->nullable();
            $table->string('registration_fee')->after('deposit')->nullable();
            $table->string('etc')->after('registration_fee')->nullable();

            $table->dropColumn('contract');
        });
    }
};
