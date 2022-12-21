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
            $table->dropColumn('registered_agent_company_name');
        });

        Schema::table('register_agents', function (Blueprint $table) {
            $table->string('company_name')->after('entity_uuid')->nullable();
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
            $table->string('registered_agent_company_name')->after('registered_agent_exists')->nullable();
        });

        Schema::table('register_agents', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
};
