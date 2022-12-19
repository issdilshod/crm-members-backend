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
            $table->renameColumn('business_mobile_number_provider', 'business_mobile_provider');
            $table->renameColumn('business_mobile_number_login', 'business_mobile_login');
            $table->renameColumn('business_mobile_number_password', 'business_mobile_password');

            $table->string('business_mobile_website')->after('business_mobile_number_type')->nullable();

            $table->string('card_on_file')->nullable();
            $table->string('card_last_four_digit')->nullable();
            $table->string('card_holder_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('business_mobile_website');

            $table->renameColumn('business_mobile_provider', 'business_mobile_number_provider');
            $table->renameColumn('business_mobile_login', 'business_mobile_number_login');
            $table->renameColumn('business_mobile_password', 'business_mobile_number_password');

            $table->dropColumn('card_on_file');
            $table->dropColumn('card_last_four_digit');
            $table->dropColumn('card_holder_name');
        });
    }
};
