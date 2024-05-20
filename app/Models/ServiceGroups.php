<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceGroups extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = ['name' , 'serviceId', 'projectTypeId', 'order'];

    public function service()
    {
        return $this->belongsTo(Services::class, 'serviceId', 'id');
    }

    public function serviceScopes()
    {
        return $this->hasMany(ServiceScopes::class, 'serviceGroupId', 'id');
    }
}
