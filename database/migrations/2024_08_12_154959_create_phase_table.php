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
        Schema::create('phases', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('problemGoalID')->constrained('problems_and_goals')->nullable();
            $table->unsignedBigInteger('transcriptId')->nullable()->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->string('title');
            $table->longText('details')->nullable();
            $table->integer('isChecked')->default(1)->comment('0: Active, 1: Inactive');
            $table->string('batchId')->nullable();
            $table->unsignedBigInteger('serial')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
