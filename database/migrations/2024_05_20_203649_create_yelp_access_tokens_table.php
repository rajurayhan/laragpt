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
        Schema::create('yelp_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('access_token');
            $table->integer('expires_in');
            $table->dateTimeTz('expires_on');
            $table->string('token_type')->default('Bearer');
            $table->string('refresh_token');
            $table->integer('refresh_token_expires_in');
            $table->dateTimeTz('refresh_token_expires_on');
            $table->string('scope');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yelp_access_tokens');
    }
};
