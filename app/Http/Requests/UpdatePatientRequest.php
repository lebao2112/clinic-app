<?php

namespace App\Http\Requests;

class UpdatePatientRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'full_name'     => 'sometimes|required|string|max:255',
            'gender'        => 'sometimes|required|in:male,female,other',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'phone'         => 'sometimes|required|string|max:20',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
        ];
    }
}