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
        Schema::create('deliberables', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('scopeOfWorkId')->constrained('scope_of_works')->nullable();
            $table->longText('deliverablesText');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliberables');
    }
};
