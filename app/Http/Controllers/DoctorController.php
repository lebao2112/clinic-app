<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Services\DoctorService;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    use ApiResponse;

    protected DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $doctors = $this->doctorService->getDoctors($request);

            return $this->successResponse(
                DoctorResource::collection($doctors),
                Message::SUCCESS,
                200,
                [
                    'current_page' => $doctors->currentPage(),
                    'last_page'    => $doctors->lastPage(),
                    'per_page'     => $doctors->perPage(),
                    'total'        => $doctors->total(),
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

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        try {
            $doctor = $this->doctorService->createDoctor($request->validated());
            $doctor->load(['user', 'specialty']); 

            return $this->successResponse(
                new DoctorResource($doctor), 
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

    public function show(Doctor $doctor): JsonResponse
    {
        try {
            $doctor->load(['user', 'specialty']);

            return $this->successResponse(
                new DoctorResource($doctor), 
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::NOT_FOUND,
                404
            );
        }
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        try {
            $updatedDoctor = $this->doctorService->updateDoctor($doctor, $request->validated());
            $updatedDoctor->load(['user', 'specialty']);

            return $this->successResponse(
                new DoctorResource($updatedDoctor), 
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        try {
            $this->doctorService->deleteDoctor($doctor);

            return $this->successResponse(
                null, 
                Message::SUCCESS
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