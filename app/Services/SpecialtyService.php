<?php

namespace App\Services;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyService
{
    public function getSpecialties(Request $request)
    {
        $query = Specialty::query();
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'ilike', '%' . $search . '%')
                  ->orWhere('description', 'ilike', '%' . $search . '%');
        }

        return $query->orderBy('id', 'desc')->paginate($request->per_page ?? 10);
    }

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