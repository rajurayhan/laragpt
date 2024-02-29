<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePromptIdNullableInConversationMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('prompt_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('prompt_id')->nullable(false)->change();
        });
    }
}
