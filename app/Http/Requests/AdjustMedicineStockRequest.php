<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustMedicineStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by EnsurePermission middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer', // Can be positive (add) or negative (subtract)
            'note'     => 'nullable|string|max:255',
        ];
    }
}