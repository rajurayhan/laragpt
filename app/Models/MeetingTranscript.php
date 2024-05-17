<?php
// app/Models/MeetingTranscript.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingTranscript extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'transcriptText',
        'projectName',
        'projectType',
        'projectTypeId',
        'company',
        'clientPhone',
        'clientEmail',
        'clientWebsite',
    ];

    public function projectSummary()
    {
        return $this->hasOne(ProjectSummary::class, 'transcriptId', 'id');
    }

    public function problemsAndGoals()
    {
        return $this->hasOne(ProblemsAndGoals::class, 'transcriptId', 'id');
    }

    public function projectProposal()
    {
        return $this->hasOne(ProjectProposal::class, 'meetingID', 'meetingID');
    }

    public function projectTypedata()
    {
        return $this->belongsTo(ProjectType::class, 'projectTypeId');
    }
    public function meetingLinks()
    {
        return $this->hasMany(MeetingLink::class, 'transcriptId', 'id');
    }
}
