<?php

namespace App\Http\Requests;

class StorePatientRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'full_name'     => 'required|string|max:255',
            'gender'        => 'required|in:male,female,other',
            'date_of_birth' => 'required|date|before:today',
            'phone'         => 'required|string|max:20', 
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
        ];
    }
}