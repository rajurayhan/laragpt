<?php
// app/Models/ScopeOfWork.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeOfWork extends Model
{
    use HasFactory;

    // protected $primaryKey = 'scopeOfWorkID';

    protected $fillable = [
        'problemGoalID',
        'transcriptId',
        'serviceScopeId',
        'scopeText',
        'title',
    ];

    public function problemsAndGoals()
    {
        return $this->belongsTo(ProblemsAndGoals::class, 'problemGoalID', 'id');
    }

    public function deliverables()
    {
        return $this->hasOne(Deliberable::class, 'scopeOfWorkId', 'id');
    }
}
