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
        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transcriptId')->nullable()->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->unsignedBigInteger('employeeRoleId')->nullable()->references('id')->on('employee_roles')->onDelete('cascade');
            $table->unsignedBigInteger('associateId')->nullable()->references('id')->on('associates')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_teams');
    }
};
