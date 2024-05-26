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
            $table->unsignedBigInteger('additionalServiceId')->nullable()->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scope_of_works', function (Blueprint $table) {
            $table->dropColumn(['additionalServiceId']);
        });
    }
};
