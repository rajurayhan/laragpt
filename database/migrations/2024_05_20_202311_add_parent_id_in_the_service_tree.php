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
        Schema::table('service_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('projectTypeId')->nullable()->references('id')->on('project_types')->onDelete('cascade');
        });

        Schema::table('service_scopes', function (Blueprint $table) {
            $table->unsignedBigInteger('projectTypeId')->nullable()->references('id')->on('project_types')->onDelete('cascade');
            $table->unsignedBigInteger('serviceId')->nullable()->references('id')->on('services')->onDelete('cascade');
        });
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->unsignedBigInteger('projectTypeId')->nullable()->references('id')->on('project_types')->onDelete('cascade');
            $table->unsignedBigInteger('serviceId')->nullable()->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('serviceGroupId')->nullable()->references('id')->on('service_groups')->onDelete('cascade');
        });
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('serviceId')->nullable()->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('serviceGroupId')->nullable()->references('id')->on('service_groups')->onDelete('cascade');
            $table->unsignedBigInteger('projectTypeId')->nullable()->references('id')->on('project_types')->onDelete('cascade');
            $table->unsignedBigInteger('serviceScopeId')->nullable()->references('id')->on('service_scopes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_groups', function (Blueprint $table) {
            $table->dropColumn('projectTypeId');
        });
        Schema::table('service_scopes', function (Blueprint $table) {
            $table->dropColumn('projectTypeId');
            $table->dropColumn('serviceId');
        });
        Schema::table('service_deliverables', function (Blueprint $table) {
            $table->dropColumn('projectTypeId');
            $table->dropColumn('serviceId');
            $table->dropColumn('serviceGroupId');
        });
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->dropColumn('projectTypeId');
            $table->dropColumn('serviceId');
            $table->dropColumn('serviceGroupId');
            $table->dropColumn('serviceScopeId');
        });

    }
};
