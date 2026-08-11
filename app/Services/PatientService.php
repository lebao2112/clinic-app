<?php

namespace App\Services;

use App\Models\Patient;

class PatientService
{
    public function getPatients($request)
    {
        $query = Patient::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);
    }

    public function createPatient(array $data)
    {
        if (!isset($data['code'])) {
            $data['code'] = 'BN-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }
        return Patient::create($data);
    }

    public function findPatientById($id)
    {
        return Patient::findOrFail($id);
    }

    public function updatePatient(Patient $patient, array $data)
    {
        $patient->update($data);
        return $patient;
    }

    public function deletePatient(Patient $patient)
    {
        return $patient->delete();
    }
}