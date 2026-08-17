<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Http\Requests\StorePrescriptionItemRequest;
use App\Http\Requests\UpdatePrescriptionItemRequest;
use App\Http\Resources\PrescriptionItemResource;
use Illuminate\Http\JsonResponse;

class PrescriptionItemController extends Controller
{
    /**
     * Add a new item to an existing prescription.
     */
    public function store(StorePrescriptionItemRequest $request, Prescription $prescription): JsonResponse
    {
        // Laravel automatically assigns the prescription_id through the items() relationship
        $item = $prescription->items()->create($request->validated());

        return response()->json([
            'message' => Message::SUCCESS,
            'data' => new PrescriptionItemResource($item)
        ], 201);
    }

    /**
     * Update an existing prescription item.
     */
    public function update(UpdatePrescriptionItemRequest $request, PrescriptionItem $prescriptionItem): JsonResponse
    {
        $prescriptionItem->update($request->validated());

        return response()->json([
            'message' => Message::SUCCESS,
            'data' => new PrescriptionItemResource($prescriptionItem)
        ]);
    }

    /**
     * Remove the specified item from the prescription.
     */
    public function destroy(PrescriptionItem $prescriptionItem): JsonResponse
    {
        $prescriptionItem->delete();

        return response()->json([
            'message' => Message::SUCCESS,
        ]);
    }
}