<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'sometimes|required|integer|min:1',
            'dosage' => 'sometimes|required|string|max:255',
            'usage_instruction' => 'nullable|string',
        ];
    }
}