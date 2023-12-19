<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingSummery extends Model
{
    use HasFactory;
    protected $fillable = [
        'transcriptText',
        'meetingSummeryText',
        'meetingName',
        'meetingType',
        'clickupLink',
        'tldvLink',
    ];
}
