<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Services\SpecialtyService;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;

class SpecialtyController extends Controller
{
    protected SpecialtyService $specialtyService;

    public function __construct(SpecialtyService $specialtyService)
    {
        $this->specialtyService = $specialtyService;
    }

    public function index()
    {
        $specialties = Specialty::paginate(10);
        $resource = SpecialtyResource::collection($specialties);

        return $this->successResponse(
            $resource->items(),
            'Specialties retrieved successfully',
            200,
            [
                'current_page' => $specialties->currentPage(),
                'last_page'    => $specialties->lastPage(),
                'per_page'     => $specialties->perPage(),
                'total'        => $specialties->total(),
            ]
        );
    }

    public function store(StoreSpecialtyRequest $request)
    {
        $specialty = $this->specialtyService->createSpecialty($request->validated());
        return $this->successResponse(new SpecialtyResource($specialty), 'Specialty created successfully', 201);
    }

    public function show(Specialty $specialty)
    {
        return $this->successResponse(new SpecialtyResource($specialty), 'Specialty retrieved successfully');
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty)
    {
        $updatedSpecialty = $this->specialtyService->updateSpecialty($specialty, $request->validated());
        return $this->successResponse(new SpecialtyResource($updatedSpecialty), 'Specialty updated successfully');
    }

    public function destroy(Specialty $specialty)
    {
        $this->specialtyService->deleteSpecialty($specialty);
        return $this->successResponse(null, 'Specialty deleted successfully');
    }
}