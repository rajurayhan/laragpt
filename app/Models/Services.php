<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'projectTypeId', 'order'];

    public function serviceGroups()
    {
        return $this->hasMany(ServiceGroups::class, 'serviceId', 'id');
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'projectTypeId');
    }
}
