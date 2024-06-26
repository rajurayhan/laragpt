<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingLinksTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transcriptId');
            $table->string('meetingLink');
            $table->longText('transcriptText');
            $table->integer('serial');
            $table->foreign('transcriptId')->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_links');
    }
}
