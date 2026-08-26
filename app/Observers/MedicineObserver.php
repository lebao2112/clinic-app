<?php

namespace App\Observers;

use App\Models\Medicine;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class MedicineObserver
{
    public function updated(Medicine $medicine): void
    {
        if ($medicine->isDirty('stock')) {
            $oldStock = $medicine->getOriginal('stock');
            $newStock = $medicine->stock;
            
            ActivityLog::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'subject_type' => 'medicine',
                'subject_id' => $medicine->id,
                'action' => 'stock_adjusted',
                'meta' => [
                    'medicine_code' => $medicine->code,
                    'medicine_name' => $medicine->name,
                    'before' => ['stock' => $oldStock],
                    'after' => ['stock' => $newStock],
                    'quantity_changed' => $newStock - $oldStock
                ]
            ]);
        }
    }
}