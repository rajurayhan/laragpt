<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteComponentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'component_name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:website_component_categories,category_id',
            'component_description' => 'sometimes|required|string',
            'component_cost' => 'sometimes|required|numeric|min:0',
        ];
    }
}
