<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAndAddForeignKeyToMeetingTranscriptsTable extends Migration
{
    public function up()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            // Rename the existing column
            $table->renameColumn('projectType', 'projectTypeId');

            // Add foreign key constraint to meeting_types table
            $table->foreign('projectTypeId')
                  ->references('id')
                  ->on('meeting_types')
                  ->onDelete('SET NULL'); // Or use any other suitable action
        });
    }

    public function down()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['projectTypeId']);

            // Rename back to the original column name
            $table->renameColumn('projectTypeId', 'projectType');
        });
    }
}
