<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateServiceScopesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_scopes', function (Blueprint $table) {
            // Remove the existing foreign key
            $table->dropForeign(['serviceId']);

            // Remove the existing column
            $table->dropColumn('serviceId');

            // Add the new column
            $table->foreignId('serviceGroupId')->constrained('service_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_scopes', function (Blueprint $table) {
            // Remove the new column
            $table->dropForeign(['serviceGroupId']);
            $table->dropColumn('serviceGroupId');

            // Add back the old column
            $table->foreignId('serviceId')->constrained('services')->onDelete('cascade');
        });
    }
}
