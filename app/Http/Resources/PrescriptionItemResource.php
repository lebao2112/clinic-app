<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medicine_id' => $this->medicine_id,
            'quantity' => $this->quantity,
            'dosage' => $this->dosage,
            'usage_instruction' => $this->usage_instruction,
        ];
    }
}