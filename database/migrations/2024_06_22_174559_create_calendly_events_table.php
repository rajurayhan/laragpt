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
        Schema::create('calendly_events', function (Blueprint $table) {
            $table->id();
            $table->string('uri')->unique();
            $table->string('name')->nullable();
            $table->text('meeting_notes_plain')->nullable();
            $table->text('meeting_notes_html')->nullable();
            $table->string('status');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('event_type')->nullable();
            $table->string('location_type')->nullable();
            $table->string('location')->nullable();
            $table->text('additional_info')->nullable();
            $table->integer('total_invitees');
            $table->integer('active_invitees');
            $table->integer('invitees_limit');
            $table->string('created_at_api');
            $table->string('updated_at_api');
            $table->string('user')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_created_at')->nullable();
            $table->string('guest_updated_at')->nullable();
            $table->string('calendar_kind')->nullable();
            $table->string('calendar_external_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendly_events');
    }
};
