<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YelpEventMessage extends Model
{
    protected $table = 'yelp_events_messages';
    use HasFactory;

    protected $fillable = [
        'leadId',
        'yelpEventId',
        'repliedUserId',
        'cursor',
        'timeCreated',
        'eventType',
        'userType',
        'eventContentText',
        'eventContentFallbackText',
        'yelpUserId',
        'yelpUserDisplayName',
    ];

    public function yelpEvent()
    {
        return $this->belongsTo(YelpEvent::class, 'yelpEventId', 'id');
    }
}
