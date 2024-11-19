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
        Schema::create('yelp_events_messages', function (Blueprint $table) {
            $table->id();
            $table->string('leadId', 350);
            $table->unsignedBigInteger('yelpEventId');
            $table->unsignedBigInteger('repliedUserId')->nullable();
            $table->string('cursor', 350)->nullable();
            $table->timestamp('timeCreated')->nullable();
            $table->string('eventType')->nullable();
            $table->string('userType')->nullable();
            $table->longText('eventContentFallbackText')->nullable();
            $table->longText('eventContentText')->nullable();
            $table->string('yelpUserId')->nullable();
            $table->string('yelpUserDisplayName')->nullable();
            $table->timestamps();

            $table->foreign('repliedUserId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('yelpEventId')->references('id')->on('yelp_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yelp_events_messages');
    }
};
