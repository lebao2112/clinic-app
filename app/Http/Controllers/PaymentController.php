<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use App\Constants\Message; 
use Illuminate\Http\Request;

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
}