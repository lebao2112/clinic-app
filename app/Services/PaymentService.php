<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Constants\Message;
use Exception;

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
        $paypalOrder = $this->paypalService->createOrder($data['amount'], $data['method']);

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
    public function capturePayment(int $paymentId): Payment
    {
        $payment = Payment::with('invoice')->findOrFail($paymentId);

        if ($payment->status !== 'pending') {
            throw new Exception(Message::PAYMENT_NOT_PENDING);
        }

        try {
            // Call PayPal Sandbox API to capture the funds
            $captureData = $this->paypalService->captureOrder($payment->provider_order_id);
            
            // Extract capture details from PayPal response structure
            $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            $captureStatus = $captureData['status'] ?? 'FAILED'; // Usually returns 'COMPLETED'

            DB::transaction(function () use ($payment, $captureId, $captureStatus) {
                if ($captureStatus === 'COMPLETED') {
                    // 1. Mark payment as completed
                    $payment->update([
                        'status' => 'completed',
                        'provider_capture_id' => $captureId,
                        'paid_at' => now(),
                    ]);

                    $invoice = $payment->invoice;
                    
                    // 2. Check total paid amount
                    $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
                    
                    // 3. If fully paid, update invoice status
                    // Note: Ensure 'final_amount' matches the column name in your invoices table
                    if ($totalPaid >= $invoice->final_amount) {
                        $invoice->update(['status' => 'paid']);
                    }
                } else {
                    // Mark payment as failed if PayPal capture was not completed
                    $payment->update([
                        'status' => 'failed',
                    ]);
                }
            });

            return $payment;

        } catch (Exception $e) {
            // If the API call fails or throws an error, mark payment as failed
            $payment->update(['status' => 'failed']);
            throw $e;
        }
    }
}