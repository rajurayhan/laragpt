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
        Schema::table('scope_of_works', function (Blueprint $table) {
            $table->unsignedBigInteger('transcriptId')->nullable()->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->unsignedBigInteger('serviceScopeId')->nullable()->references('id')->on('service_scopes')->onDelete('cascade');
            $table->string('title');
            $table->longText('scopeText')->nullable()->change();
            $table->integer('isChecked')->default(1)->comment('0: Active, 1: Inactive');
            $table->string('batchId')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scope_of_works', function (Blueprint $table) {
            $table->dropColumn(['transcriptId','title','serviceScopeId','isChecked','batchId']);
            $table->dropSoftDeletes();
        });
    }
};
