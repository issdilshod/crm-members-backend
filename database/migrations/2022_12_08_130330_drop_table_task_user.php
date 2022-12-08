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
        Schema::dropIfExists('task_to_users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('task_to_users', function (Blueprint $table) {
            $table->string('task_uuid');
            $table->string('user_uuid');
            $table->string('department_uuid');
            $table->tinyInteger('group')->default(0);
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }
};
