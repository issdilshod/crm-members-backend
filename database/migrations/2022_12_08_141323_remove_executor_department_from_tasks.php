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
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_executor_user_uuid_foreign');
            $table->dropForeign('tasks_department_uuid_foreign');

            $table->dropColumn('executor_user_uuid');
            $table->dropColumn('department_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('executor_user_uuid')->nullable()->after('task_name');
            $table->string('department_uuid')->nullable()->after('executor_user_uuid');

            $table->foreign('executor_user_uuid')->references('uuid')->on('users');
            $table->foreign('department_uuid')->references('uuid')->on('departments');
        });
    }
};
