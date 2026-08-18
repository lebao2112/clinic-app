<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use ApiResponse;

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Store a newly created invoice in storage.
     *
     * @param StoreInvoiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            // Data is already validated; duplicates will automatically return 422
            $data = $request->validated();
            
            $invoice = $this->invoiceService->createInvoice($data);

            return $this->successResponse(
                $invoice, 
                Message::INVOICE_CREATED_SUCCESS, 
                201
            );
            
        } catch (Exception $e) {
            Log::error('Invoice Creation Error: ' . $e->getMessage());
            
            return $this->errorResponse(
                Message::CREATE_INVOICE_FAILED, 
                500, 
                [$e->getMessage()]
            );
        }
    }
}