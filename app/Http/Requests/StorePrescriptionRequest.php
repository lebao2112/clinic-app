<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    public function rules(): array
    {
        $prescriptionId = $this->route('prescription') ?? $this->route('id');

        return [
            'examination_id' => [
                'sometimes',
                'required',
                'exists:examinations,id',
                Rule::unique('prescriptions', 'examination_id')->ignore($prescriptionId)
            ],
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.medicine_id' => 'required_with:items|exists:medicines,id|distinct',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.dosage' => 'required_with:items|string|max:255',
            'items.*.usage_instruction' => 'nullable|string',
        ];
    }
}