<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceScopes extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceId'];

    public function service()
    {
        return $this->belongsTo(Services::class, 'serviceId', 'id');
    }

    public function serviceDeliverables()
    {
        return $this->hasMany(ServiceDeliverables::class, 'serviceScopeId', 'id');
    }
}
