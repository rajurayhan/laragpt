<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimationTask extends Model
{
    protected $table = 'estimation_tasks';
    use HasFactory;

    protected $fillable = [
        'transcriptId',
        'problemGoalId',
        'additionalServiceId',
        'serviceDeliverableTasksId',
        'estimateHours',
        'estimationTasksParentId',
        'serviceDeliverableTasksParentId',
        'title',
        'details',
        'isChecked',
        'batchId',
        'serviceDeliverablesId',
        'deliverableId',
        'employeeRoleId',
        'userId',
        'associateId',
        'hourlyRate',
        'isManualAssociated',
        'serial',
        'isManual',
    ];

    protected $casts = [
        'isManualAssociated' => 'boolean',
    ];
    public function associate()
    {
        return $this->belongsTo(Associate::class, 'associateId', 'id');
    }
    public function deliverable()
    {
        return $this->belongsTo(Deliberable::class, 'deliverableId', 'id');
    }
    public function additionalServiceInfo()
    {
        return $this->belongsTo(Services::class, 'additionalServiceId', 'id');
    }
}
