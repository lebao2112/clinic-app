<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\Invoice;
use App\Constants\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class InvoiceService
{
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $examination = Examination::with('prescription.prescriptionItems.medicine')->findOrFail($data['examination_id']);

            $medicineTotal = 0;
            if ($examination->prescription && $examination->prescription->prescriptionItems) {
                foreach ($examination->prescription->prescriptionItems as $item) {
                    $unitPrice = $item->medicine->price ?? 0;
                    $medicineTotal += ($item->quantity * $unitPrice);
                }
            }

            $examinationFee = (float) env('EXAMINATION_FEE', 100000);
            $subtotal = $examinationFee + $medicineTotal;
            $discount = $data['discount'] ?? 0;
            $total = max($subtotal - $discount, 0);

            $invoiceCode = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            return Invoice::create([
                'examination_id' => $examination->id,
                'invoice_code'   => $invoiceCode,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'status'         => 'unpaid', 
                'issued_at'      => now(),
            ]);
        });
    }

    public function updateDiscount($id, float $discount): Invoice
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'unpaid') {
            throw new Exception(Message::INVOICE_CANNOT_BE_MODIFIED);
        }

        $total = max($invoice->subtotal - $discount, 0);

        $invoice->update([
            'discount' => $discount,
            'total'    => $total,
        ]);

        return $invoice;
    }

    public function cancelInvoice($id): Invoice
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'unpaid') {
            throw new Exception(Message::INVOICE_CANNOT_BE_MODIFIED);
        }

        $invoice->update([
            'status' => 'cancelled',
        ]);

        return $invoice;
    }
}