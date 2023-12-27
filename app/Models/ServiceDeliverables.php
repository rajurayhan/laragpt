<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeliverables extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceScopeId'];

    public function serviceScope()
    {
        return $this->belongsTo(ServiceScopes::class, 'serviceScopeId', 'id');
    }

    public function serviceDeliverableTasks()
    {
        return $this->belongsTo(ServiceDeliverableTasks::class, 'serviceDeliverableId', 'id');
    }
}
