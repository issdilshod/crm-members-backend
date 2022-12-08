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
            $table->dropColumn('company_uuid');

            $table->string('task_name')->nullable()->after('user_uuid');
            $table->string('executor_user_uuid')->nullable()->after('task_name');
            $table->string('department_uuid')->nullable()->after('executor_user_uuid');

            $table->foreign('executor_user_uuid')->references('uuid')->on('users');
            $table->foreign('department_uuid')->references('uuid')->on('departments');
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
            $table->dropColumn('task_name');
            $table->dropColumn('executor_user_uuid');
            $table->dropColumn('department_uuid');

            $table->string('company_uuid')->nullable();
        });
    }
};
