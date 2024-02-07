<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAndAddForeignKeyToMeetingTranscriptsTable extends Migration
{
    public function up()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->unsignedBigInteger('projectTypeId')->nullable();
                $table->foreign('projectTypeId')->references('id')->on('project_types');
        });
    }

    public function down()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['projectTypeId']);

            // Drop back to the original column name
            $table->dropColumn('projectTypeId');
        });
    }
}
