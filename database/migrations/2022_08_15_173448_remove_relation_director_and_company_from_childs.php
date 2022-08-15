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
        Schema::table('emails', function (Blueprint $table) {
            //
            $table->dropForeign("emails_entity_uuid_foreign");
        });

        Schema::table('addresses', function (Blueprint $table) {
            //
            $table->dropForeign("addresses_entity_uuid_foreign");
        });

        Schema::table('files', function (Blueprint $table) {
            //
            $table->dropForeign("files_entity_uuid_foreign");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            //
            $table->foreign("entity_uuid")->references('uuid')->on('directors');
        });

        Schema::table('addresses', function (Blueprint $table) {
            //
            $table->foreign("entity_uuid")->references('uuid')->on('directors');
        });

        Schema::table('files', function (Blueprint $table) {
            //
            $table->foreign("entity_uuid")->references('uuid')->on('directors');
        });
    }
};
