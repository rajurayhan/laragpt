<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('meeting_summeries', function (Blueprint $table) {
            // Check if the column doesn't exist before adding
            if (!Schema::hasColumn('meeting_summeries', 'createdById')) {
                $table->unsignedBigInteger('createdById')->nullable(); 
                $table->foreign('createdById')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('meeting_summeries', function (Blueprint $table) {
            // Check if the foreign key exists before dropping
            if (Schema::hasColumn('meeting_summeries', 'createdById') && 
                Schema::hasForeignKey('meeting_summeries', 'meeting_summeries_createdById_foreign')) {
                $table->dropForeign(['createdById']);
                $table->dropColumn('createdById');
            }
        });
    }
};
