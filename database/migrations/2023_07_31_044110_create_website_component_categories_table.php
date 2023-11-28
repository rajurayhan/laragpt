<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteComponentCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('website_component_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('website_component_categories');
    }
};
