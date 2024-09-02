<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliberable extends Model
{
    use HasFactory;

    protected $fillable = [
        'scopeOfWorkId',
        'deliverablesText',
        'transcriptId',
        'serviceScopeId',
        'problemGoalId',
        'title',
        'isChecked',
        'batchId',
        'serviceDeliverablesId',
        'additionalServiceId',
        'serial',
    ];

    public function scopeOfWork()
    {
        return $this->belongsTo(ScopeOfWork::class, 'scopeOfWorkId', 'id');
    }

    public function additionalServiceInfo()
    {
        return $this->belongsTo(Services::class, 'additionalServiceId', 'id');
    }
}
