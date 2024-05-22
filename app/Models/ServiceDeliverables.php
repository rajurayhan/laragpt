<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeliverables extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceScopeId','projectTypeId','serviceId','serviceGroupId', 'order'];

    public function serviceScope()
    {
        return $this->belongsTo(ServiceScopes::class, 'serviceScopeId', 'id');
    }

    public function serviceDeliverableTasks()
    {
        return $this->hasmany(ServiceDeliverableTasks::class, 'serviceDeliverableId', 'id');
    }
}
