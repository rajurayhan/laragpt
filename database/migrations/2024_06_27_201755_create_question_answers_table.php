<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('answer')->nullable();
            $table->unsignedBigInteger('questionId')->nullable()->references('id')->on('questions')->onDelete('cascade');
            $table->unsignedBigInteger('transcriptId')->nullable()->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->unsignedBigInteger('problemGoalId')->nullable()->references('id')->on('problems_and_goals')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answers');
    }
};
