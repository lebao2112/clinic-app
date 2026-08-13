<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MedicineService
{
    public function getMedicines(): LengthAwarePaginator
    {
        return Medicine::orderBy('created_at', 'desc')->paginate(15);
    }

    public function createMedicine(array $data): Medicine
    {
        return Medicine::create($data);
    }

    public function findMedicineById(int $id): Medicine
    {
        return Medicine::findOrFail($id);
    }

    public function updateMedicine(Medicine $medicine, array $data): Medicine
    {
        $medicine->update($data);
        
        return $medicine;
    }

    public function deleteMedicine(Medicine $medicine): void
    {
        $medicine->delete();
    }
}