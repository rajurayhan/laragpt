<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $primaryKey = 'project_id';

    protected $fillable = ['project_name', 'project_description', 'total_cost'];

    public function components()
    {
        return $this->belongsToMany(WebsiteComponent::class, 'project_components', 'project_id', 'component_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}

