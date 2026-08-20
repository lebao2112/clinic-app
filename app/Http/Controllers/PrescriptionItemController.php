<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Http\Requests\StorePrescriptionItemRequest;
use App\Http\Requests\UpdatePrescriptionItemRequest;
use App\Http\Resources\PrescriptionItemResource;
use App\Services\PrescriptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PrescriptionItemController extends Controller
{
    use ApiResponse;

    protected PrescriptionService $prescriptionService;

    // Inject the service
    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }

    /**
     * Add a new item to an existing prescription.
     */
    public function store(StorePrescriptionItemRequest $request, Prescription $prescription): JsonResponse
    {
        $item = $this->prescriptionService->addItemToPrescription($prescription, $request->validated());

        return $this->successResponse(
            new PrescriptionItemResource($item),
            Message::SUCCESS,
            201
        );
    }

    /**
     * Update an existing prescription item.
     */
    public function update(UpdatePrescriptionItemRequest $request, PrescriptionItem $prescriptionItem): JsonResponse
    {
        $updatedItem = $this->prescriptionService->updatePrescriptionItem($prescriptionItem, $request->validated());

        return $this->successResponse(
            new PrescriptionItemResource($updatedItem),
            Message::SUCCESS
        );
    }

    /**
     * Remove the specified item from the prescription.
     */
    public function destroy(PrescriptionItem $prescriptionItem): JsonResponse
    {
        $this->prescriptionService->removePrescriptionItem($prescriptionItem);

        return $this->successResponse(null, Message::SUCCESS);
    }
}