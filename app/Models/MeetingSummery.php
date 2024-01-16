<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingSummery extends Model
{
    use HasFactory;
    use CreatedByTrait;

    protected $fillable = [
        'transcriptText',
        'meetingSummeryText',
        'meetingName',
        'meetingType',
        'clickupLink',
        'tldvLink',
        'createdById'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'createdById', 'id');
    }
}
