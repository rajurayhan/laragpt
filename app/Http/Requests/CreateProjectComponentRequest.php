<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectComponentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'components' => 'required|array',
            'components.*.component_id' => 'required|exists:website_components,id',
            'components.*.quantity' => 'required|integer|min:1',
        ];
    }
}
