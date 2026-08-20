<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrescriptionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the prescription ID from the URL: /api/prescriptions/{prescription}/items
        $prescriptionId = $this->route('prescription')->id ?? $this->route('prescription');

        return [
            'medicine_id' => [
                'required',
                'exists:medicines,id', 
                // Prevent duplicate medicine_id for the same prescription_id
                Rule::unique('prescription_items')->where(function ($query) use ($prescriptionId) {
                    return $query->where('prescription_id', $prescriptionId);
                })
            ],
            'quantity' => 'required|integer|min:1',
            'dosage' => 'required|string|max:255',
            'usage_instruction' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'medicine_id.unique' => 'This medicine is already in the prescription. Please update the quantity instead.',
        ];
    }
}