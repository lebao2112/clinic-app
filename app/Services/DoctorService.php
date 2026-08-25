<?php

namespace App\Services;

use App\Models\Doctor;

class DoctorService
{
    public function getDoctors($request)
    {
        $query = Doctor::with(['user', 'specialty']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {

                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'ilike', '%' . $search . '%');
                })
                ->orWhere('license_number', 'ilike', '%' . $search . '%');
            });
        }
        if ($request->has('specialty_id') && !empty($request->specialty_id)) {
            $query->where('specialty_id', $request->specialty_id);
        }

        return $query->orderBy('id', 'desc')->paginate($request->per_page ?? 10);
    }

    public function createDoctor(array $data)
    {
        return Doctor::create($data);
    }

    public function updateDoctor(Doctor $doctor, array $data)
    {
        $doctor->update($data);
        return $doctor;
    }

    public function deleteDoctor(Doctor $doctor)
    {
        $doctor->delete();
    }
}