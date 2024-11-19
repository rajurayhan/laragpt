<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YelpEvent extends Model
{
    protected $table = 'yelp_events';
    use HasFactory;

    protected $fillable = [
        'title',
        'leadId',
        'lastCursor',
        'assistantId',
        'threadId',
    ];
}
