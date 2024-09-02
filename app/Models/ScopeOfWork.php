<?php
// app/Models/ScopeOfWork.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScopeOfWork extends Model
{
    use HasFactory, SoftDeletes;

    // protected $primaryKey = 'scopeOfWorkID';

    protected $fillable = [
        'problemGoalID',
        'transcriptId',
        'phaseId',
        'serviceScopeId',
        'scopeText',
        'title',
        'transcriptId',
        'isChecked',
        'batchId',
        'serial',
        'additionalServiceId',
        'serviceGroupId',
    ];

    public function problemsAndGoals()
    {
        return $this->belongsTo(ProblemsAndGoals::class, 'problemGoalID', 'id');
    }

    public function deliverables()
    {
        return $this->hasMany(ServiceDeliverables::class, 'serviceScopeId', 'serviceScopeId');
    }
    public function meetingTranscript()
    {
        return $this->belongsTo(MeetingTranscript::class, 'transcriptId', 'id');
    }
    public function phaseInfo()
    {
        return $this->belongsTo(Phase::class, 'phaseId', 'id');
    }
    public function additionalServiceInfo()
    {
        return $this->belongsTo(Services::class, 'additionalServiceId', 'id');
    }

}
