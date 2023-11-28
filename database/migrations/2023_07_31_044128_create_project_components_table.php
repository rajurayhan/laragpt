<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectComponentsTable extends Migration
{
    public function up()
    {
        Schema::create('project_components', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('component_id');
            $table->integer('quantity');
            $table->decimal('total_component_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('component_id')->references('component_id')->on('website_components')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_components');
    }
};
