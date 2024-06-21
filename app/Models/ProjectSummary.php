<?php
// app/Models/ProjectSummary.php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectSummary extends Model
{
    use HasFactory, SoftDeletes;
    use CreatedByTrait;

    // protected $primaryKey = 'summaryID';

    protected $fillable = [
        'transcriptId',
        'summaryText',
        'createdById'
    ];

    public function meetingTranscript()
    {
        return $this->belongsTo(MeetingTranscript::class, 'transcriptId', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'createdById', 'id');
    }
}
