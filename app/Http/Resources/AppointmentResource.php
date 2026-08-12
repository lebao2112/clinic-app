<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'patient_id'   => $this->patient_id,
            'doctor_id'    => $this->doctor_id,
            'scheduled_at' => $this->scheduled_at ? $this->scheduled_at->format('Y-m-d H:i:s') : null,
            'status'       => $this->status,
            'reason'       => $this->reason,
            'patient'      => new PatientResource($this->whenLoaded('patient')),
            'doctor'       => new DoctorResource($this->whenLoaded('doctor')),
            'created_at'   => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}