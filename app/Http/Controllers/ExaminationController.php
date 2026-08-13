<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExaminationRequest;
use App\Http\Resources\ExaminationResource;
use App\Services\ExaminationService;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;

class ExaminationController extends Controller
{
    use ApiResponse;

    protected ExaminationService $examinationService;

    public function __construct(ExaminationService $examinationService)
    {
        $this->examinationService = $examinationService;
    }

    /**
     * Store a newly created examination in storage.
     */
    public function store(StoreExaminationRequest $request): JsonResponse
    {
        $examination = $this->examinationService->createExamination($request->validated());
        return $this->successResponse(
            new ExaminationResource($examination),
            Message::SUCCESS,
            201
        );
    }
}