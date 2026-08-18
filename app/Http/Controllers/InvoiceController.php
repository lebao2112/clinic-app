<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceDiscountRequest;
use App\Http\Resources\InvoiceResource;
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
            $data = $request->validated();
            
            $invoice = $this->invoiceService->createInvoice($data);

            return $this->successResponse(
                new InvoiceResource($invoice), 
                Message::INVOICE_CREATED_SUCCESS, 
                201
            );
            
        } catch (Exception $e) {
            Log::error(Message::LOG_INVOICE_CREATION_ERROR . $e->getMessage());
            
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
            $updatedInvoice = $this->invoiceService->updateDiscount($id, $request->validated('discount'));

            return $this->successResponse(
                new InvoiceResource($updatedInvoice), 
                Message::INVOICE_DISCOUNT_UPDATED,
                200
            );
        } catch (Exception $e) {
            if ($e->getMessage() === Message::INVOICE_CANNOT_BE_MODIFIED) {
                return $this->errorResponse($e->getMessage(), 422);
            }
            
            Log::error(Message::LOG_INVOICE_UPDATE_DISCOUNT_ERROR . $e->getMessage());
            return $this->errorResponse(
                Message::ERROR ?? Message::INTERNAL_SERVER_ERROR, 
                500, 
                [$e->getMessage()]
            );
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
            $cancelledInvoice = $this->invoiceService->cancelInvoice($id);

            return $this->successResponse(
                new InvoiceResource($cancelledInvoice), 
                Message::INVOICE_CANCELLED,
                200
            );
        } catch (Exception $e) {
            if ($e->getMessage() === Message::INVOICE_CANNOT_BE_MODIFIED) {
                return $this->errorResponse($e->getMessage(), 422);
            }
            
            Log::error(Message::LOG_INVOICE_CANCEL_ERROR . $e->getMessage());
            return $this->errorResponse(
                Message::ERROR ?? Message::INTERNAL_SERVER_ERROR, 
                500, 
                [$e->getMessage()]
            );
        }
    }
}