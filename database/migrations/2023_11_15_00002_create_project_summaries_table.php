<?php
// database/migrations/xxxx_xx_xx_create_project_summaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectSummariesTable extends Migration
{
    public function up()
    {
        Schema::create('project_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcriptId')->constrained('meeting_transcripts');
            $table->longText('summaryText');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_summaries');
    }
}
