<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteComponent extends Model
{
    protected $primaryKey = 'component_id';

    protected $fillable = ['component_name', 'category_id', 'component_description', 'component_cost'];

    public function category()
    {
        return $this->belongsTo(WebsiteComponentCategory::class, 'category_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_components', 'component_id', 'project_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function projectComponents()
    {
        return $this->hasMany(ProjectComponent::class, 'component_id');
    }
}

