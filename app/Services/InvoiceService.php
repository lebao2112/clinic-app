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
    // Define consultation fee constant (can also be loaded from config file)
    public const EXAMINATION_FEE = 150000; 

    /**
     * Create invoice with automatic cost calculation.
     *
     * @param array $data
     * @return Invoice
     * @throws Exception
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // Eager load relationships to prevent N+1 queries
            $examination = Examination::with('prescription.prescriptionItems')->findOrFail($data['examination_id']);

            // 1. Calculate medicine total: SUM(qty * price)
            $medicineTotal = 0;
            if ($examination->prescription && $examination->prescription->prescriptionItems) {
                foreach ($examination->prescription->prescriptionItems as $item) {
                    $medicineTotal += ($item->quantity * $item->price);
                }
            }

            // 2. Calculate subtotal and final total
            $subtotal = $medicineTotal + self::EXAMINATION_FEE;
            $discount = $data['discount'] ?? 0;
            $total = $subtotal - $discount;

            // Prevent negative total
            if ($total < 0) {
                $total = 0;
            }

            // 3. Generate unique invoice code
            $invoiceCode = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            // 4. Create and return the invoice
            return Invoice::create([
                'examination_id' => $examination->id,
                'invoice_code'   => $invoiceCode,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'status'         => 'unpaid', // Default status
                'issued_at'      => now(),
            ]);
        });
    }

    /**
     * Update the invoice discount securely.
     * 
     * @param Invoice $invoice
     * @param float $discount
     * @return Invoice
     * @throws Exception
     */
    public function updateDiscount(Invoice $invoice, float $discount): Invoice
    {
        // Prevent updates if the invoice is no longer unpaid
        if ($invoice->status !== 'unpaid') {
            throw new Exception(Message::INVOICE_CANNOT_BE_MODIFIED);
        }

        // Calculate new total
        $total = $invoice->subtotal - $discount;

        $invoice->update([
            'discount' => $discount,
            'total'    => max($total, 0), // Prevent negative total
        ]);

        return $invoice;
    }

    /**
     * Cancel the invoice safely.
     * 
     * @param Invoice $invoice
     * @return Invoice
     * @throws Exception
     */
    public function cancelInvoice(Invoice $invoice): Invoice
    {
        // Prevent cancellation if the invoice is no longer unpaid
        if ($invoice->status !== 'unpaid') {
            throw new Exception(Message::INVOICE_CANNOT_BE_MODIFIED);
        }

        $invoice->update([
            'status' => 'cancelled',
        ]);

        return $invoice;
    }
}