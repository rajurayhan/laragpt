<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('conversationId');
            $table->unsignedBigInteger('conversationDetailId');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversationId')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('conversationDetailId')->references('id')->on('conversation_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookmarks');
    }
}
