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
        Schema::create('estimation_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transcriptId')->nullable()->references('id')->on('meeting_transcripts')->onDelete('cascade');
            $table->unsignedBigInteger('problemGoalId')->nullable()->references('id')->on('problems_and_goals')->onDelete('cascade');
            $table->unsignedBigInteger('additionalServiceId')->nullable()->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('serviceDeliverableTasksId')->nullable()->references('id')->on('service_deliverable_tasks')->onDelete('cascade');
            $table->foreignId('employeeRoleId')->nullable()->constrained('employee_roles')->onDelete('cascade');
            $table->unsignedDouble('estimateHours', 8, 2)->default(0.00);
            $table->unsignedBigInteger('userId')->nullable();
            $table->unsignedBigInteger('serviceDeliverableTasksParentId')->nullable();
            $table->foreign('serviceDeliverableTasksParentId')->references('id')->on('service_deliverable_tasks')->onDelete('cascade');
            $table->unsignedBigInteger('estimationTasksParentId')->nullable();
            $table->foreign('estimationTasksParentId')->references('id')->on('estimation_tasks')->onDelete('cascade');
            $table->unsignedBigInteger('serviceDeliverablesId')->nullable()->references('id')->on('service_deliverables')->onDelete('cascade');
            $table->string('title');
            $table->longText('details')->nullable();
            $table->integer('isChecked')->default(1)->comment('0: Active, 1: Inactive');
            $table->string('batchId')->nullable();
            $table->unsignedBigInteger('deliverableId')->nullable()->references('id')->on('deliberables')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimation_tasks');
    }
};
