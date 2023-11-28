<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectComponentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'components' => 'required|array',
            'components.*.component_id' => 'required|exists:website_components,id',
            'components.*.quantity' => 'required|integer|min:1',
        ];
    }
}
