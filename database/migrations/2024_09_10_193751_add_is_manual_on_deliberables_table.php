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
            $table->tinyInteger('isManual')->default(0)->comment('0: Automatic, 1: Manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliberables', function (Blueprint $table) {
            $table->dropColumn('isManual');
        });
    }
};
