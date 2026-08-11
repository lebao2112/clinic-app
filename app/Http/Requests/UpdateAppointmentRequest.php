<?php

namespace App\Http\Requests;

class UpdateAppointmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'patient_id'   => 'sometimes|required|integer|exists:patients,id',
            'doctor_id'    => 'sometimes|required|integer|exists:doctors,id',
            'scheduled_at' => 'sometimes|required|date_format:Y-m-d H:i:s|after:now',
            'reason'       => 'nullable|string|max:255',
            'status'       => 'sometimes|required|in:scheduled,confirmed,cancelled,completed',
        ];
    }
}