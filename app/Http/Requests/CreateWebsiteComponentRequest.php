<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWebsiteComponentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'component_name' => 'required|string|max:255',
            'category_id' => 'required|exists:website_component_categories,category_id',
            'component_description' => 'required|string',
            'component_cost' => 'required|numeric|min:0',
        ];
    }
}

