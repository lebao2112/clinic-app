<?php

namespace App\Services;

use App\Models\Specialty;

class SpecialtyService
{
    public function createSpecialty(array $data)
    {
        return Specialty::create($data);
    }

    public function updateSpecialty(Specialty $specialty, array $data)
    {
        $specialty->update($data);
        return $specialty;
    }

    public function deleteSpecialty(Specialty $specialty)
    {
        $specialty->delete();
    }
}