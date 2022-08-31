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
        Schema::table('users', function (Blueprint $table) {
            $table->string('department_uuid')->nullable()->after('uuid');
            $table->string('role_uuid')->nullable()->after('uuid');
            $table->foreign('department_uuid', 'fk_users_to_department')->references('uuid')->on('departments');
            $table->foreign('role_uuid', 'fk_users_to_role')->references('uuid')->on('roles');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fk_users_to_department']);
            $table->dropForeign(['fk_users_to_role']);
        });
    }
};
