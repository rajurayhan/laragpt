<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectTypeIdToServicesTable extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            // Add the projectTypeId column
            $table->unsignedBigInteger('projectTypeId')->nullable();

            // Add foreign key constraint to meeting_types table
            $table->foreign('projectTypeId')
                  ->references('id')
                  ->on('project_types')
                  ->onDelete('SET NULL'); // Or use any other suitable action
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['projectTypeId']);

            // Drop the projectTypeId column
            $table->dropColumn('projectTypeId');
        });
    }
}
