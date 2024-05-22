<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceScopes extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceGroupId', 'serviceId', 'projectTypeId', 'order'];

    public function serviceGroup()
    {
        return $this->belongsTo(ServiceGroups::class, 'serviceGroupId', 'id');
    }

    public function serviceDeliverables()
    {
        return $this->hasMany(ServiceDeliverables::class, 'serviceScopeId', 'id');
    }
}
