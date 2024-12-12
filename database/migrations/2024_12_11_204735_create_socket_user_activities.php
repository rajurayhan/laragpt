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
        Schema::create('socket_user_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('activity_type')->nullable();
            $table->string('document_id')->nullable();
            $table->string('document_related_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'activity_type','document_id']);
            $table->index(['user_id', 'activity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socket_user_activities');
    }
};
