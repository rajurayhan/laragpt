<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectComponentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'project_id' => $this->project_id,
            'component_id' => $this->component_id,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

