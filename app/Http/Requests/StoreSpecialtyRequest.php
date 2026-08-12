<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest; 

class StoreSpecialtyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:specialties,name',
            'description' => 'nullable|string',
        ];
    }
}