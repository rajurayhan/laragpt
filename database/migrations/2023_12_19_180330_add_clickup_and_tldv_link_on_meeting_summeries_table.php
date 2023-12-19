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
        Schema::table('meeting_summeries', function (Blueprint $table) {
             $table->string('clickupLink')->after('meetingType')->nullable();
             $table->string('tldvLink')->after('clickupLink')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_summeries', function (Blueprint $table) {
            $table->dropColumn('clickupLink');
            $table->dropColumn('tldvLink');
        });
    }
};
