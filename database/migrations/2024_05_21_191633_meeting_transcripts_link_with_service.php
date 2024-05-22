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
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->unsignedBigInteger('serviceId')->nullable()->references('id')->on('services')->onDelete('cascade');
            if (Schema::hasColumn('meeting_transcripts', 'projectType')) {
                $table->dropColumn('projectType');
            }
            if (Schema::hasColumn('meeting_transcripts', 'projectTypeId')) {
                $table->dropForeign(['projectTypeId']);
                $table->dropColumn('projectTypeId');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_transcripts', function (Blueprint $table) {
            $table->dropColumn('serviceId');
        });
    }
};
