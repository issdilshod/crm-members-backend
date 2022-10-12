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
        Schema::table('emails', function (Blueprint $table){
            $table->dropForeign(['hosting_uuid']);
            $table->string('hosting_uuid')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table){
            $table->string('hosting_uuid')->nullable(false)->change();
            $table->foreign('hosting_uuid')->references('uuid')->on('hostings');
        });
    }
};
