<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_name' => 'required|string|max:255',
            'project_description' => 'required|string',
            'total_cost' => 'required|numeric|min:0',
        ];
    }
}
