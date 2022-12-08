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
            $table->string('vo_provider')->nullable()->after('user_uuid');
            $table->string('vo_website')->nullable()->after('vo_provider');
            $table->string('vo_contact_person_name')->nullable()->after('vo_website');
            $table->string('vo_contact_person_phone_number')->nullable()->after('vo_contact_person_name');
            $table->string('online_account')->nullable()->after('vo_contact_person_phone_number');
            $table->string('online_account_username')->nullable()->after('online_account');
            $table->string('online_account_password')->nullable()->after('online_account_username');
            $table->string('card_on_file')->nullable()->after('online_account_password');
            $table->string('card_last_four_digit')->nullable()->after('card_on_file');
            $table->string('card_holder_name')->nullable()->after('card_last_four_digit');
            $table->string('monthly_payment_amount')->nullable()->after('card_holder_name');
            $table->string('contract')->nullable()->after('monthly_payment_amount');
            $table->string('contract_terms')->nullable()->after('contract');

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
            $table->dropColumn('vo_provider');
            $table->dropColumn('vo_website');
            $table->dropColumn('vo_contact_person_name');
            $table->dropColumn('vo_contact_person_phone_number');
            $table->dropColumn('online_account');
            $table->dropColumn('online_account_username');
            $table->dropColumn('online_account_password');
            $table->dropColumn('card_on_file');
            $table->dropColumn('card_last_four_digit');
            $table->dropColumn('card_holder_name');
            $table->dropColumn('monthly_payment_amount');
            $table->dropColumn('contract');
            $table->dropColumn('contract_terms');
        });
    }
};
