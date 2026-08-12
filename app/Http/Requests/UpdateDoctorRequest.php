<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class UpdateDoctorRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialty_id'   => 'sometimes|required|integer|exists:specialties,id',
            'license_number' => 'sometimes|required|string|max:255',
            'bio'            => 'nullable|string',
        ];
    }
}