<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteComponentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'component_id' => $this->component_id,
            'component_name' => $this->component_name,
            'category_id' => $this->category_id,
            'component_description' => $this->component_description,
            'component_cost' => $this->component_cost,
            'category' => new WebsiteComponentCategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

