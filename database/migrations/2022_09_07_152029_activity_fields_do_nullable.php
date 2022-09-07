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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('entity_uuid')->nullable()->default('')->change();
            $table->string('device')->nullable()->default('')->change();
            $table->string('ip')->nullable()->default('')->change();
            $table->string('description')->nullable()->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('entity_uuid')->nullable(false)->default(null)->change();
            $table->string('device')->nullable(false)->default(null)->change();
            $table->string('ip')->nullable(false)->default(null)->change();
            $table->string('description')->nullable(false)->default(null)->change();
        });
    }
};
