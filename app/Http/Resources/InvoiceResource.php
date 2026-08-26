<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $examination = $this->relationLoaded('examination') && !($this->examination instanceof \Illuminate\Http\Resources\MissingValue) 
            ? $this->examination 
            : null;

        if (!$examination && $this->examination_id) {
            $examination = \App\Models\Examination::with('prescription.prescriptionItems.medicine')->find($this->examination_id);
        }

        $prescription = $examination ? $examination->prescription : null;
        
        $prescriptionItems = collect();
        if ($prescription) {
            if ($prescription->relationLoaded('prescriptionItems')) {
                $prescriptionItems = $prescription->prescriptionItems;
            } else {
                $prescriptionItems = $prescription->prescriptionItems()->with('medicine')->get();
            }
        }

        $medicineTotal = 0;
        $formattedItems = $prescriptionItems->map(function ($item) use (&$medicineTotal) {
            $unitPrice = $item->medicine->price ?? 0;
            $totalPrice = $item->quantity * $unitPrice;
            $medicineTotal += $totalPrice;

            return [
                'id'                => $item->id,
                'medicine_name'     => $item->medicine->name ?? 'N/A',
                'quantity'          => $item->quantity,
                'unit_price'        => $unitPrice,
                'total_price'       => $totalPrice,
                'dosage'            => $item->dosage,
                'usage_instruction' => $item->usage_instruction,
            ];
        });

        $examinationFee = (float) env('EXAMINATION_FEE', 100000);

        $paidAmount = $this->payments()->where('status', 'completed')->sum('amount');
        $remainingAmount = max($this->total - $paidAmount, 0);

        return [
            'id'             => $this->id,
            'invoice_code'   => $this->invoice_code,
            'examination_id' => $this->examination_id,
            'subtotal'       => $this->subtotal,
            'discount'       => $this->discount,
            'total'          => $this->total,
            'paid_amount'    => $paidAmount,     
            'remaining_amount' => $remainingAmount,
            'status'         => $this->status,
            'issued_at'      => $this->issued_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'breakdown'      => [
                'examination_fee' => $examinationFee,
                'medicine_total'  => $medicineTotal,
                'items'           => $formattedItems,
            ],
        ];
    }
}
