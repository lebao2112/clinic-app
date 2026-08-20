<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExaminationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'doctor_id' => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'diagnosis' => $this->diagnosis,
            'notes' => $this->notes,
            'examined_at' => $this->examined_at,
            'created_at' => $this->created_at,
        ];
    }
}