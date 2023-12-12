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
        Schema::create('meeting_summeries', function (Blueprint $table) {
            $table->id();
            $table->longText('transcriptText');
            $table->longText('meetingSummeryText');
            $table->longText('meetingName');
            $table->integer('meetingType');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_summeries');
    }
};
