<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDeliverableTasks extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'serviceDeliverableId', 'name', 'description', 'cost'];

    public function serviceDeliverable()
    {
        return $this->belongsTo(ServiceDeliverables::class, 'serviceDeliverableId', 'id');
    }
}
