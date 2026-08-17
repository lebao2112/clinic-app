<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'examination_id' => $this->examination_id,
            'doctor_id' => $this->doctor_id,
            'notes' => $this->notes,
            // Automatically format items using PrescriptionItemResource when items are loaded
            'items' => PrescriptionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}