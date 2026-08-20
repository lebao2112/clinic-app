<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Constants\Message;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('APPOINTMENTS.CREATE');
    }

    public function rules()
    {
        return [
            'patient_id'   => 'required|exists:patients,id',
            'doctor_id'    => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after_or_equal:today',
            'reason'       => 'nullable|string|max:255',
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => Message::FORBIDDEN . 'APPOINTMENTS.CREATE'
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