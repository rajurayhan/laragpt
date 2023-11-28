<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteComponentCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

