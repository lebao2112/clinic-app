<?php

namespace App\Services;

use App\Models\Doctor;

class DoctorService
{
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