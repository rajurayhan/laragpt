<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteComponentsTable extends Migration
{
    public function up()
    {
        Schema::create('website_components', function (Blueprint $table) {
            $table->id('component_id');
            $table->string('component_name');
            $table->unsignedBigInteger('category_id');
            $table->text('component_description');
            $table->decimal('component_cost', 10, 2);
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('website_component_categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('website_components');
    }
};
