<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'project_description' => $this->project_description,
            'total_cost' => $this->total_cost,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
