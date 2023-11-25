<?php

// app/Models/ProblemsAndGoals.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemsAndGoals extends Model
{
    use HasFactory;

    // protected $primaryKey = 'problemGoalID';

    protected $fillable = [
        'transcriptId',
        'problemGoalText',
    ];

    public function meetingTranscript()
    {
        return $this->belongsTo(MeetingTranscript::class, 'transcriptId', 'id');
    }

    public function projectOverview()
    {
        return $this->hasOne(ProjectOverview::class, 'transcriptId', 'id');
    }

    public function scopeOfWork()
    {
        return $this->hasOne(ScopeOfWork::class, 'transcriptId', 'id');
    }
}
