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

    ];
}
