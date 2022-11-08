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
        Schema::table('future_companies', function (Blueprint $table) {
            $table->string('future_website_link')->after('revival_fee')->nullable();
            $table->string('recommended_director_uuid')->after('future_website_link')->nullable();
            $table->string('revived')->after('recommended_director_uuid')->nullable();
            $table->string('db_report_number')->after('revived')->nullable();
            $table->text('comment')->after('db_report_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('future_companies', function (Blueprint $table) {
            $table->dropColumn('future_website_link');
            $table->dropColumn('recommended_director_uuid');
            $table->dropColumn('revived');
            $table->dropColumn('db_report_number');
            $table->dropColumn('comment');
        });
    }
};
