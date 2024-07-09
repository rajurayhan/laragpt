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
        'serviceId',
        'company',
        'clientPhone',
        'clientEmail',
        'clientWebsite',
        'meetingLinks',
        'assistantId',
        'threadId',
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

    public function serviceInfo()
    {
        return $this->belongsTo(Services::class, 'serviceId');
    }
    public function meetingLinks()
    {
        return $this->hasMany(MeetingLink::class, 'transcriptId', 'id');
    }
}
