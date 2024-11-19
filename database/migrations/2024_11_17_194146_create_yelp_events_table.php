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
        Schema::create('yelp_events', function (Blueprint $table) {
            $table->id();
            $table->string('leadId', 350);
            $table->string('title')->nullable();
            $table->string('lastCursor', 350)->nullable();
            $table->string('assistantId')->nullable()->index();
            $table->string('threadId')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yelp_events');
    }
};
