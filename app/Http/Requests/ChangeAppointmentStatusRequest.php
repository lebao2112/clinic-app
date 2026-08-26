<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Constants\Message;

class ChangeAppointmentStatusRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
    
        if (!$user || !$user->role) {
        return false;
        }

        return $user->role->permissions->contains('name', 'APPOINTMENTS.UPDATESTATUS');
    }

    public function rules()
    {
        return [
            'status' => 'required|in:confirmed,cancelled,completed',
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => Message::FORBIDDEN . 'APPOINTMENTS.UPDATESTATUS'
        ], 403));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => Message::VALIDATION_FAILED,
            'errors'  => $validator->errors()
        ], 422));
    }
}