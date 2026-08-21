<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    protected PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Process payment initialization and create pending record.
     */
    public function initializePayment(int $invoiceId, array $data): array
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // 1. Calculate remaining amount
        $paidAmount = $invoice->payments()->where('status', 'completed')->sum('amount');
        
        // Assuming your Invoice model has a 'final_amount' or similar field representing the total after discount
        // Replace 'final_amount' with your actual column name (e.g., total_amount - discount)
        $remainingAmount = $invoice->final_amount - $paidAmount; 

        // 2. Prevent overpayment -> Throws 422 Unprocessable Entity
        if ($data['amount'] > $remainingAmount) {
            throw ValidationException::withMessages([
                'amount' => ["The payment amount cannot exceed the remaining balance ($remainingAmount)."]
            ]);
        }

        // 3. Call PayPal Sandbox API to create an Order
        $paypalOrder = $this->paypalService->createOrder($data['amount']);

        // 4. Save payment record in database with 'pending' status
        $payment = Payment::create([
            'invoice_id'        => $invoice->id,
            'amount'            => $data['amount'],
            'method'            => $data['method'], // 'paypal' or 'visa'
            'status'            => 'pending',
            'provider'          => 'paypal',
            'provider_order_id' => $paypalOrder['id'],
        ]);

        // 5. Extract the approval URL from PayPal's response for the frontend to redirect
        $approvalUrl = collect($paypalOrder['links'])->where('rel', 'approve')->first()['href'] ?? null;

        return [
            'payment_id'      => $payment->id,
            'paypal_order_id' => $paypalOrder['id'],
            'approval_url'    => $approvalUrl
        ];
    }
}