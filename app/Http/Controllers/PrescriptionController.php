<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Services\PrescriptionService;
use App\Models\Examination;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    use ApiResponse;

    protected PrescriptionService $prescriptionService;

    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }

    /**
     * Display a listing of the prescriptions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $prescriptions = $this->prescriptionService->getPrescriptions($request);
            
            return $this->successResponse(
                PrescriptionResource::collection($prescriptions),
                Message::SUCCESS,
                200,
                [
                    'current_page' => $prescriptions->currentPage(),
                    'last_page'    => $prescriptions->lastPage(),
                    'per_page'     => $prescriptions->perPage(),
                    'total'        => $prescriptions->total(),
                ]
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $examination = Examination::find($data['examination_id']);

            if (!$examination || !$examination->doctor_id) {
                return $this->errorResponse(Message::FORBIDDEN, 403);
            }
            $data['doctor_id'] = $examination->doctor_id;

            $prescription = $this->prescriptionService->createPrescription($data);

            return $this->successResponse(
                new PrescriptionResource($prescription),
                Message::SUCCESS,
                201
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    /**
     * Display the specified prescription.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $prescription = $this->prescriptionService->findPrescriptionById($id);
            
            return $this->successResponse(
                new PrescriptionResource($prescription), 
                Message::SUCCESS,
                200
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::NOT_FOUND,
                404
            );
        }
    }

   public function update(StorePrescriptionRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            
            $existingPrescription = \App\Models\Prescription::findOrFail($id);
            $examinationId = $data['examination_id'] ?? $existingPrescription->examination_id;

            $examination = Examination::find($examinationId);

            if (!$examination || !$examination->doctor_id) {
                return $this->errorResponse(Message::FORBIDDEN, 403);
            }

            $data['examination_id'] = $examinationId;
            $data['doctor_id'] = $examination->doctor_id;

            $prescription = $this->prescriptionService->updatePrescription($id, $data);

            return $this->successResponse(
                new PrescriptionResource($prescription),
                Message::SUCCESS,
                200
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }
}