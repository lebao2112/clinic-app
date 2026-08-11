<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Constants\Message;

class PatientController extends Controller
{
    use ApiResponse;

    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request)
    {
        $patients = $this->patientService->getPatients($request);
        return $this->successResponse(PatientResource::collection($patients), Message::SUCCESS, 200, [
            'total' => $patients->total(),
            'current_page' => $patients->currentPage(),
        ]);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = $this->patientService->createPatient($request->validated());
        return $this->successResponse(new PatientResource($patient), Message::SUCCESS, 201);
    }

    public function show($id)
    {
        $patient = $this->patientService->findPatientById($id);
        return $this->successResponse(new PatientResource($patient));
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        $patient = $this->patientService->findPatientById($id);
        $updatedPatient = $this->patientService->updatePatient($patient, $request->validated());
        return $this->successResponse(new PatientResource($updatedPatient));
    }

    public function destroy($id)
    {
        $patient = $this->patientService->findPatientById($id);
        $this->patientService->deletePatient($patient);
        return $this->successResponse(null, Message::SUCCESS);
    }
}