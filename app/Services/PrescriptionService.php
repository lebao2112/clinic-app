<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrescriptionService
{
    /**
     * Create a new prescription along with its items using a transaction.
     */
    public function createPrescription(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the main prescription record
            $prescription = Prescription::create([
                'examination_id' => $data['examination_id'],
                'doctor_id' => $data['doctor_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Process items and deduct stock
            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->processMedicineDeduction($prescription, $itemData);
                }
            }

            return $prescription->load('items');
        });
    }

    /**
     * Add a single item to an existing prescription with stock deduction.
     */
    public function addItemToPrescription(Prescription $prescription, array $itemData)
    {
        return DB::transaction(function () use ($prescription, $itemData) {
            return $this->processMedicineDeduction($prescription, $itemData);
        });
    }

    /**
     * Internal method to lock medicine, check stock, deduct, and create item.
     */
    protected function processMedicineDeduction(Prescription $prescription, array $itemData)
    {
        // Lock the medicine row to prevent race conditions
        $medicine = Medicine::where('id', $itemData['medicine_id'])->lockForUpdate()->first();

        // Check if medicine exists and has enough stock
        if (!$medicine || $medicine->stock < $itemData['quantity']) {
            // Throwing this exception will automatically rollback the transaction and return 422
            throw ValidationException::withMessages([
                'medicine_id' => "Not enough stock for medicine ID: {$itemData['medicine_id']}. Available: " . ($medicine->stock ?? 0)
            ]);
        }

        // Deduct inventory
        $medicine->decrement('stock', $itemData['quantity']);

        // Create and return the prescription item
        return $prescription->items()->create([
            'medicine_id' => $itemData['medicine_id'],
            'quantity' => $itemData['quantity'],
            'dosage' => $itemData['dosage'],
            'usage_instruction' => $itemData['usage_instruction'] ?? null,
        ]);
    }
}