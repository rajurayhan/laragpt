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
        Schema::table('deliberables', function (Blueprint $table) {
            $table->unsignedBigInteger('additionalServiceId')->nullable()->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('serviceDeliverablesId')->nullable()->references('id')->on('service_deliverables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliberables', function (Blueprint $table) {
            $table->dropColumn(['additionalServiceId','serviceDeliverablesId']);
        });
    }
};
