<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Constants\Message;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Always allow validation to proceed
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        // Throw an exception with a standardized JSON envelope for 422 errors
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => Message::VALIDATION_FAILED,
            'errors'  => $validator->errors() // Attach the specific field errors
        ], 422));
    }
}