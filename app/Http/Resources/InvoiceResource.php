<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_code'   => $this->invoice_code,
            'examination_id' => $this->examination_id,
            'subtotal'       => $this->subtotal,
            'discount'       => $this->discount,
            'total'          => $this->total,
            'status'         => $this->status,
            'issued_at'      => $this->issued_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}