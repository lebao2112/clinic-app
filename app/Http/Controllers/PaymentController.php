<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use App\Constants\Message; 
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    use ApiResponse; 

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a pending payment order.
     */
    public function store(StorePaymentRequest $request, int $invoiceId)
    {
        try {
            $result = $this->paymentService->initializePayment($invoiceId, $request->validated());
            
            return $this->successResponse(
                $result, 
                Message::PAYMENT_ORDER_CREATED, 
                201
            );
        } catch (\Exception $e) {
            throw $e; 
        }
    }

    /**
     * Handle PayPal return callback to capture payment and redirect back to frontend.
     */
    public function handleReturn(Request $request)
    {
        try {
            $token = $request->query('token');
            
            if ($token) {
                $payment = Payment::where('provider_order_id', $token)->first();
                if ($payment && $payment->status === 'pending') {
                    $this->paymentService->capturePayment($payment->id);
                }
            }
            
            return redirect('http://localhost:8000/invoices?payment=success');
        } catch (\Exception $e) {
            return redirect('http://localhost:8000/invoices?payment=failed');
        }
    }

    /**
     * Handle PayPal cancel callback and redirect back to frontend.
     */
    public function handleCancel(Request $request)
    {
        return redirect('http://localhost:8000/invoices?payment=cancelled');
    }

    public function capture(int $id)
    {
        try {
            $payment = $this->paymentService->capturePayment($id);
            
            if ($payment->status === 'completed') {
                return $this->successResponse(
                    $payment, 
                    Message::PAYMENT_CAPTURED_SUCCESS, 
                    200
                );
            }
            
            return $this->errorResponse(Message::PAYMENT_CAPTURE_FAILED, 400);
            
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}