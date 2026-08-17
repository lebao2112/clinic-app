<?php

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    /**
     * Create a new prescription along with its items using a transaction.
     */
    public function createPrescription(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create the main prescription record
            $prescription = Prescription::create([
                'examination_id' => $data['examination_id'],
                'doctor_id' => $data['doctor_id'], // Assigned from auth user
                'notes' => $data['notes'] ?? null,
            ]);

            // If an array of items is provided, insert them
            if (!empty($data['items'])) {
                $itemsToInsert = [];
                foreach ($data['items'] as $item) {
                    $itemsToInsert[] = [
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'dosage' => $item['dosage'],
                        'usage_instruction' => $item['usage_instruction'] ?? null,
                    ];
                }
                
                // Save all items linked to this prescription
                $prescription->items()->createMany($itemsToInsert);
            }

            return $prescription->load('items');
        });
    }
}