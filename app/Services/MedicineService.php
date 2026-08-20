<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

    public function adjustStock(int $id, array $data): Medicine
    {
        $medicine = $this->findMedicineById($id);
        
        $newStock = $medicine->stock + $data['quantity'];

        if ($newStock < 0) {
            throw new InvalidArgumentException('Stock cannot be negative after adjustment.');
        }

        $medicine->update(['stock' => $newStock]);

        // Write to activity log
        // If your team uses a specific package like spatie/laravel-activitylog, you can replace this.
        // For now, using standard Laravel Log as requested.
        Log::info('Medicine stock adjusted', [
            'medicine_id'      => $medicine->id,
            'quantity_changed' => $data['quantity'],
            'new_stock'        => $newStock,
            'note'             => $data['note'] ?? null,
            'user_id'          => Auth::id(),
        ]);

        return $medicine;
    }
}