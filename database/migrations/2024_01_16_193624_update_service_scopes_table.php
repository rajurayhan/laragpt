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
            // Check if the 'serviceId' column exists before removing it
            if (Schema::hasColumn('service_scopes', 'serviceId')) {
                // Remove the existing foreign key
                $table->dropForeign(['serviceId']);

                // Remove the existing column
                $table->dropColumn('serviceId');
            }

            // Check if the 'serviceGroupId' column does not exist before adding it
            if (!Schema::hasColumn('service_scopes', 'serviceGroupId')) {
                // Add the new column
                $table->foreignId('serviceGroupId')->constrained('service_groups')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_scopes', function (Blueprint $table) {
            // Check if the 'serviceGroupId' column exists before removing it
            if (Schema::hasColumn('service_scopes', 'serviceGroupId')) {
                // Remove the new column
                $table->dropForeign(['serviceGroupId']);
                $table->dropColumn('serviceGroupId');
            }

            // Check if the 'serviceId' column does not exist before adding it back
            if (!Schema::hasColumn('service_scopes', 'serviceId')) {
                // Add back the old column
                $table->foreignId('serviceId')->constrained('services')->onDelete('cascade');
            }
        });
    }
}
