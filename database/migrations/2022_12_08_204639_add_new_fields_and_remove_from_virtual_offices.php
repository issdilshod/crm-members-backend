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
            $table->dropColumn('vo_provider');
            $table->dropColumn('vo_provider_domain');
            $table->string('vo_contact_person_email')->nullable()->after('vo_contact_person_phone_number');
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
            $table->dropColumn('vo_contact_person_email');

            $table->string('vo_provider')->nullable()->after('user_uuid');
            $table->string('vo_provider_domain')->nullable()->after('vo_provider_name');
        });
    }
};
