<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeliverableTasks extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceDeliverableId', 'order', 'description', 'cost', 'parentTaskId', 'employeeRoleId'];

    public function serviceDeliverable()
    {
        return $this->belongsTo(ServiceDeliverables::class, 'serviceDeliverableId', 'id');
    }

    public function parentTask()
    {
        return $this->belongsTo(ServiceDeliverableTasks::class, 'parentTaskId');
    }
    public function subTasks()
    {
        return $this->hasMany(ServiceDeliverableTasks::class, 'parentTaskId');
    }

    public function employeeRole()
    {
        return $this->belongsTo(EmployeeRoles::class, 'employeeRoleId');
    }
}
