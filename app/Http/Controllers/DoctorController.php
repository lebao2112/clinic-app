<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Services\DoctorService;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    protected DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialty']);

        if ($request->has('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->paginate(10);
        $resource = DoctorResource::collection($doctors);

        return $this->successResponse(
            $resource->items(),
            'Doctors retrieved successfully',
            200,
            [
                'current_page' => $doctors->currentPage(),
                'last_page'    => $doctors->lastPage(),
                'per_page'     => $doctors->perPage(),
                'total'        => $doctors->total(),
            ]
        );
    }

    public function store(StoreDoctorRequest $request)
    {
        $doctor = $this->doctorService->createDoctor($request->validated());
        $doctor->load(['user', 'specialty']); 
        return $this->successResponse(new DoctorResource($doctor), 'Doctor profile created successfully', 201);
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'specialty']);
        return $this->successResponse(new DoctorResource($doctor), 'Doctor profile retrieved successfully');
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $updatedDoctor = $this->doctorService->updateDoctor($doctor, $request->validated());
        $updatedDoctor->load(['user', 'specialty']);
        return $this->successResponse(new DoctorResource($updatedDoctor), 'Doctor profile updated successfully');
    }

    public function destroy(Doctor $doctor)
    {
        $this->doctorService->deleteDoctor($doctor);
        return $this->successResponse(null, 'Doctor profile deleted successfully');
    }
}