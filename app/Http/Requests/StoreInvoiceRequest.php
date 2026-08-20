<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Require cashier role (Assuming you have a role/permission system)
        // return auth()->user()->hasRole('cashier');
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Ensure examination exists and has no existing invoice (Returns 422 if failed)
            'examination_id' => [
                'required',
                'integer',
                'exists:examinations,id',
                'unique:invoices,examination_id'
            ],
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}