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
        //
        Schema::table('activities', function (Blueprint $table){
            $table->string('user_uuid')->change();
            $table->string('entity_uuid')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('activities', function (Blueprint $table){
            $table->string('entity_uuid', 100)->change();
            $table->string('user_uuid', 100)->change();
        });
    }
};
