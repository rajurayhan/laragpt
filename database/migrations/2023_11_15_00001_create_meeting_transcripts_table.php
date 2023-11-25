<?php
// database/migrations/xxxx_xx_xx_create_meeting_transcripts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingTranscriptsTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_transcripts', function (Blueprint $table) {
            $table->id();
            $table->text('transcriptText');
            $table->text('projectName')->nullable();
            $table->text('clientPhone')->nullable();
            $table->text('clientEmail')->nullable();
            $table->text('clientWebsite')->nullable();
            $table->text('')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_transcripts');
    }
}
