<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExaminationRequest;
use App\Http\Requests\UpdateExaminationRequest; 
use App\Http\Resources\ExaminationResource;
use App\Services\ExaminationService;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ExaminationController extends Controller
{
    use ApiResponse;

    protected ExaminationService $examinationService;

    public function __construct(ExaminationService $examinationService)
    {
        $this->examinationService = $examinationService;
    }
    public function index(Request $request): JsonResponse
    {
        try {
            $examinations = $this->examinationService->getExaminations($request);
            
            return $this->successResponse(
                ExaminationResource::collection($examinations), 
                Message::SUCCESS, 
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
    public function store(StoreExaminationRequest $request): JsonResponse
    {
        try {
            $examination = $this->examinationService->createExamination($request->validated());
            
            return $this->successResponse(
                new ExaminationResource($examination),
                Message::SUCCESS,
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
    public function show(int $id): JsonResponse
    {
        try {
            $examination = $this->examinationService->findExaminationById($id);
            return $this->successResponse(new ExaminationResource($examination), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
    public function update(UpdateExaminationRequest $request, int $id): JsonResponse
    {
        try {
            $examination = $this->examinationService->findExaminationById($id);
            $updatedExamination = $this->examinationService->updateExamination($examination, $request->validated());
            
            return $this->successResponse(new ExaminationResource($updatedExamination), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
    public function destroy(int $id): JsonResponse
    {
        try {
            $examination = $this->examinationService->findExaminationById($id);
            $this->examinationService->deleteExamination($examination);
            
            return $this->successResponse(null, Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
}