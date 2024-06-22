<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendlyEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uri',
        'name',
        'meeting_notes_plain',
        'meeting_notes_html',
        'status',
        'start_time',
        'end_time',
        'event_type',
        'location_type',
        'location',
        'additional_info',
        'total_invitees',
        'active_invitees',
        'invitees_limit',
        'created_at_api',
        'updated_at_api',
        'user',
        'user_email',
        'user_name',
        'guest_email',
        'guest_created_at',
        'guest_updated_at',
        'calendar_kind',
        'calendar_external_id',
    ];
}
