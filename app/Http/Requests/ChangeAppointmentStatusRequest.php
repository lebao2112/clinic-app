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
        return $this->user() && $this->user()->can('APPOINTMENTS.UPDATE');
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
            'message' => Message::FORBIDDEN . 'APPOINTMENTS.UPDATE'
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