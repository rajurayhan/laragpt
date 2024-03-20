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
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->foreignId('employeeRoleId')->nullable()->constrained('employee_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->dropForeign(['employeeRoleId']);
            $table->dropColumn('employeeRoleId');
        });
    }
};
