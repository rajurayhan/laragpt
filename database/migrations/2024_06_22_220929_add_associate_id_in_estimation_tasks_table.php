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
        Schema::table('estimation_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('associateId')->nullable()->references('id')->on('associates')->onDelete('cascade');
            $table->unsignedFloat('hourlyRate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimation_tasks', function (Blueprint $table) {
            $table->dropColumn('associateId');
            $table->dropColumn('hourlyRate');
        });
    }
};
