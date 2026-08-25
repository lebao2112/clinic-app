<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Services\SpecialtyService;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class SpecialtyController extends Controller
{
    use ApiResponse;

    protected SpecialtyService $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $specialties = $this->specialtyService->getSpecialties($request);
            $resource = SpecialtyResource::collection($specialties);

            return $this->successResponse(
                $resource->items(),
                Message::SUCCESS,
                200,
                [
                    'current_page' => $specialties->currentPage(),
                    'last_page'    => $specialties->lastPage(),
                    'per_page'     => $specialties->perPage(),
                    'total'        => $specialties->total(),
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

    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        try {
            $specialty = $this->specialtyService->createSpecialty($request->validated());

            return $this->successResponse(
                new SpecialtyResource($specialty),
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

    public function show(Specialty $specialty): JsonResponse
    {
        try {
            return $this->successResponse(
                new SpecialtyResource($specialty),
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

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): JsonResponse
    {
        try {
            $updatedSpecialty = $this->specialtyService->updateSpecialty($specialty, $request->validated());

            return $this->successResponse(
                new SpecialtyResource($updatedSpecialty),
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

    public function destroy(Specialty $specialty): JsonResponse
    {
        try {
            $this->specialtyService->deleteSpecialty($specialty);

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