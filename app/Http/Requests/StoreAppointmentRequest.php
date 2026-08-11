<?php

namespace App\Http\Requests;

class StoreAppointmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'patient_id'   => 'required|integer|exists:patients,id',
            'doctor_id'    => 'required|integer|exists:doctors,id',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s|after:now',
            'reason'       => 'nullable|string|max:255',
        ];
    }
}