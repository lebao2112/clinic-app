<?php

namespace App\Services;

use App\Models\Medicine;
use App\Constants\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MedicineService
{
    public function getMedicines(Request $request = null): LengthAwarePaginator
    {
        $query = Medicine::query();

        if ($request && $request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'ilike', '%' . $search . '%')
                  ->orWhere('code', 'ilike', '%' . $search . '%');
        }
        return $query->orderBy('created_at', 'desc')->paginate($request ? $request->per_page : 15);
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
            throw new InvalidArgumentException(Message::MEDICINE_STOCK_NEGATIVE);
        }

        $medicine->update(['stock' => $newStock]);

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