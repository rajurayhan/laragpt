<?php

// app/Models/ProjectComponent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectComponent extends Model
{
    protected $fillable = ['project_id', 'component_id', 'quantity', 'total_component_cost']; // Add 'total_component_cost' to the fillable array

    // Define the relationship with the Project model
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Define the relationship with the WebsiteComponent model
    public function component()
    {
        return $this->belongsTo(WebsiteComponent::class, 'component_id');
    }

    // Calculate and store the total_component_cost before saving or updating
    public static function boot()
    {
        parent::boot();

        static::saving(function ($projectComponent) {
            $totalCost = $projectComponent->quantity * $projectComponent->component->component_cost;
            $projectComponent->total_component_cost = $totalCost;
        });

        static::updating(function ($projectComponent) {
            $totalCost = $projectComponent->quantity * $projectComponent->component->component_cost;
            $projectComponent->total_component_cost = $totalCost;
        });
    }
}
