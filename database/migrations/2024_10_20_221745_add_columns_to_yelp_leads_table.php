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
        Schema::table('yelp_leads', function (Blueprint $table) {
            $table->string('yelp_user_id');
            $table->string('yelp_lead_id')->unique();
            $table->string('user_display_name');
            $table->longText('initial_query_and_answers')->nullable();
            $table->boolean('marked_as_replied')->default(false);
            $table->timestamp('marked_as_replied_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('yelp_leads', function (Blueprint $table) {
            $table->dropColumn([
                'yelp_user_id',
                'user_display_name',
                'yelp_lead_id',
                'initial_query_and_answers',
                'marked_as_replied',
                'marked_as_replied_at'
            ]);
        });
    }
};
