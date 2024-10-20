<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YelpLead extends Model
{
    protected $fillable = [
        'yelp_user_id',
        'yelp_lead_id',
        'initial_query_and_answers',
        'marked_as_replied',
        'marked_as_replied_at',
        'user_display_name'
    ];
    use HasFactory;
}
