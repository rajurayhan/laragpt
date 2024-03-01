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
        // Services
        Schema::table('services', function (Blueprint $table) {
            $table->integer('order')->after('name'); // Adjust 'after' to specify the position of the new column
        });
        // Service Groups
        Schema::table('service_groups', function (Blueprint $table) {
            $table->integer('order')->after('name'); // Adjust 'after' to specify the position of the new column
        });
        // Service Scopes
        Schema::table('service_scopes', function (Blueprint $table) {
            $table->integer('order')->after('name'); // Adjust 'after' to specify the position of the new column
        });
        // Service Deliverables
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->integer('order')->after('name'); // Adjust 'after' to specify the position of the new column
        });
        // Service Deliverables Tasks
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->integer('order')->after('name'); // Adjust 'after' to specify the position of the new column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('service_groups', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('service_scopes', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->dropColumn('order');
        });
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
