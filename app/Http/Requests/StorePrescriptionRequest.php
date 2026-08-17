<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    public function rules(): array
    {
        return [
            // Ensure examination exists and hasn't been prescribed yet
            'examination_id' => 'required|exists:examinations,id|unique:prescriptions,examination_id',
            'notes' => 'nullable|string',
            
            // Validate the items array if provided
            'items' => 'nullable|array',
            'items.*.medicine_id' => 'required_with:items|exists:medicines,id|distinct',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.dosage' => 'required_with:items|string|max:255',
            'items.*.usage_instruction' => 'nullable|string',
        ];
    }
}