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
        Schema::table('task_to_users', function (Blueprint $table){
            $table->foreign('task_uuid', 'fk_task_to_users')->references('uuid')->on('tasks');
            $table->foreign('user_uuid', 'fk_user_to_tasks')->references('uuid')->on('users');
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
        Schema::table('task_to_users', function (Blueprint $table){
            $table->dropForeign(['fk_task_to_users']);
            $table->dropForeign(['fk_user_to_tasks']);
        });
    }
};
