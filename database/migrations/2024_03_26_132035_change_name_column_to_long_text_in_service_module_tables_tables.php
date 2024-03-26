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
        // Change column type for 'name' column in 'services' table
        Schema::table('services', function (Blueprint $table) {
            $table->longText('name')->change();
        });

        // Change column type for 'name' column in 'service_groups' table
        Schema::table('service_groups', function (Blueprint $table) {
            $table->longText('name')->change();
        });

        // Change column type for 'name' column in 'service_scopes' table
        Schema::table('service_scopes', function (Blueprint $table) {
            $table->longText('name')->change();
        });

        // Change column type for 'name' column in 'service_deliverables' table
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->longText('name')->change();
        });

        // Change column type for 'name' column in 'service_deliverable_tasks' table
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->longText('name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change column type for 'name' column in 'services' table
        Schema::table('services', function (Blueprint $table) {
            $table->string('name')->change();
        });

        // Change column type for 'name' column in 'service_groups' table
        Schema::table('service_groups', function (Blueprint $table) {
            $table->string('name')->change();
        });

        // Change column type for 'name' column in 'service_scopes' table
        Schema::table('service_scopes', function (Blueprint $table) {
            $table->string('name')->change();
        });

        // Change column type for 'name' column in 'service_deliverables' table
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->string('name')->change();
        });

        // Change column type for 'name' column in 'service_deliverable_tasks' table
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
