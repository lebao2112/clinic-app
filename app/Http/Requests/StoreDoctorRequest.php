<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use App\Models\Role;

class StoreDoctorRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'unique:doctors,user_id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    $doctorRole = Role::where('name', 'DOCTOR')->first();
                    
                    if (!$user || !$doctorRole || $user->role_id !== $doctorRole->id) {
                        $fail('Doctor profiles can only be created for users with the DOCTOR role.');
                    }
                }
            ],
            'specialty_id'   => 'required|integer|exists:specialties,id',
            'license_number' => 'required|string|max:255',
            'bio'            => 'nullable|string',
        ];
    }
}