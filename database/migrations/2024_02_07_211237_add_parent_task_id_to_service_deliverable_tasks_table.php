<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentTaskIdToServiceDeliverableTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('parentTaskId')->nullable();
            $table->foreign('parentTaskId')->references('id')->on('service_deliverable_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_deliverable_tasks', function (Blueprint $table) {
            $table->dropForeign(['parentTaskId']);
            $table->dropColumn('parentTaskId');
        });
    }
}
