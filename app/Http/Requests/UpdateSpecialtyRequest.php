<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class UpdateSpecialtyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        
        $specialtyId = $this->route('specialty')->id ?? $this->route('specialty');

        return [
            'name'        => 'sometimes|required|string|max:255|unique:specialties,name,' . $specialtyId,
            'description' => 'nullable|string',
        ];
    }
}