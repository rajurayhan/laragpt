<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_name' => 'sometimes|required|string|max:255',
            'project_description' => 'sometimes|required|string',
            'total_cost' => 'sometimes|required|numeric|min:0',
        ];
    }
}

