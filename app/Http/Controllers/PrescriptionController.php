<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Services\PrescriptionService;
use Illuminate\Http\JsonResponse;

class PrescriptionController extends Controller
{
    protected PrescriptionService $prescriptionService;

    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Assign the currently authenticated doctor
        $data['doctor_id'] = $request->user()->id;

        $prescription = $this->prescriptionService->createPrescription($data);

        return response()->json([
            'message' => Message::SUCCESS,
            'data' => new PrescriptionResource($prescription)
        ], 201);
    }
}