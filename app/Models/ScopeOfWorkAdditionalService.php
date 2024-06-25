<?php
// app/Models/ScopeOfWork.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScopeOfWorkAdditionalService extends Model
{
    protected $table = 'scope_of_work_additional_services';
    use HasFactory, SoftDeletes;

    // protected $primaryKey = 'scopeOfWorkID';

    protected $fillable = [
        'problemGoalId',
        'transcriptId',
        'selectedServiceId',
    ];

    public function problemsAndGoals()
    {
        return $this->belongsTo(ProblemsAndGoals::class, 'problemGoalID', 'id');
    }
    public function meetingTranscript()
    {
        return $this->belongsTo(MeetingTranscript::class, 'transcriptId', 'id');
    }
    public function serviceInfo()
    {
        return $this->belongsTo(Services::class, 'selectedServiceId', 'id');
    }
}
