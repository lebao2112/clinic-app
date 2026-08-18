<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceDiscountRequest;
use App\Models\Invoice;
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

    /**
     * Update invoice discount.
     *
     * @param UpdateInvoiceDiscountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateInvoiceDiscountRequest $request, $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $updatedInvoice = $this->invoiceService->updateDiscount($invoice, $request->validated('discount'));

            return $this->successResponse(
                $updatedInvoice, 
                Message::INVOICE_DISCOUNT_UPDATED,
                200
            );
        } catch (Exception $e) {
            if ($e->getMessage() === Message::INVOICE_CANNOT_BE_MODIFIED) {
                return $this->errorResponse($e->getMessage(), 422);
            }
            Log::error('Invoice Update Discount Error: ' . $e->getMessage());
            return $this->errorResponse(Message::ERROR ?? 'Internal Server Error', 500, [$e->getMessage()]);
        }
    }

    /**
     * Cancel the invoice.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus($id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $cancelledInvoice = $this->invoiceService->cancelInvoice($invoice);

            return $this->successResponse(
                $cancelledInvoice, 
                Message::INVOICE_CANCELLED,
                200
            );
        } catch (Exception $e) {
            if ($e->getMessage() === Message::INVOICE_CANNOT_BE_MODIFIED) {
                return $this->errorResponse($e->getMessage(), 422);
            }
            Log::error('Invoice Cancel Error: ' . $e->getMessage());
            return $this->errorResponse(Message::ERROR ?? 'Internal Server Error', 500, [$e->getMessage()]);
        }
    }
}