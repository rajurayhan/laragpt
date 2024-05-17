<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTranscriptTextFromMeetingTranscriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->dropColumn('transcriptText');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->longText('transcriptText')->nullable();
        });
    }
}
