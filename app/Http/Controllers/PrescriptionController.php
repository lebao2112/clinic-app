<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Services\PrescriptionService;
use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $prescriptions = $this->prescriptionService->getPrescriptions($request);
        
        return $this->successResponse(
            PrescriptionResource::collection($prescriptions),
            Message::SUCCESS,
            200
        );
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Find the doctor record associated with the currently authenticated user's ID
        $doctor = Doctor::where('user_id', $request->user()->id)->first();

        if (!$doctor) {
            return $this->errorResponse(Message::FORBIDDEN . ' Doctor profile not found for this account.', 403);
        }

        // Assign the correct doctor ID from the doctors table
        $data['doctor_id'] = $doctor->id;

        $prescription = $this->prescriptionService->createPrescription($data);

        return $this->successResponse(
            new PrescriptionResource($prescription),
            Message::SUCCESS,
            201
        );
    }

    /**
     * Display the specified prescription.
     */
    public function show(int $id): JsonResponse
    {
        $prescription = $this->prescriptionService->findPrescriptionById($id);
        
        return $this->successResponse(
            new PrescriptionResource($prescription), 
            Message::SUCCESS,
            200
        );
    }
}