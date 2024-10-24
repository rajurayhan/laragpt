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
        Schema::create('prompt_shared_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promptId')->constrained('prompts')->onDelete('cascade');
            $table->foreignId('teamId')->constrained('teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_shared_team');
    }
};
