<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Constants\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrescriptionService
{
    /**
     * Get a paginated list of prescriptions.
     */
    public function getPrescriptions(Request $request)
    {
        $query = Prescription::with(['doctor.user', 'examination.patient', 'items.medicine']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('examination.patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('full_name', 'ilike', '%' . $search . '%')
                                 ->orWhere('code', 'ilike', '%' . $search . '%');
                })
                ->orWhereHas('doctor.user', function ($doctorQuery) use ($search) {
                    $doctorQuery->where('name', 'ilike', '%' . $search . '%');
                });
            });
        }

        return $query->orderBy('id', 'desc')->paginate($request->input('per_page', 15));
    }

    /**
     * Find a prescription by ID or fail.
     */
    public function findPrescriptionById(int $id): Prescription
    {
        return Prescription::with(['doctor', 'examination', 'items.medicine'])->findOrFail($id);
    }

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
     * Update an existing prescription item and adjust stock based on delta quantity or medicine change.
     */
    public function updatePrescriptionItem(PrescriptionItem $item, array $data)
    {
        return DB::transaction(function () use ($item, $data) {
            $newMedicineId = $data['medicine_id'] ?? $item->medicine_id;
            $newQuantity = $data['quantity'] ?? $item->quantity;

            // Case 1: Medicine ID changed
            if ($newMedicineId != $item->medicine_id) {
                // Restore stock for old medicine
                $oldMedicine = Medicine::where('id', $item->medicine_id)->lockForUpdate()->first();
                if ($oldMedicine) {
                    $oldMedicine->increment('stock', $item->quantity);
                }

                // Deduct stock for new medicine
                $newMedicine = Medicine::where('id', $newMedicineId)->lockForUpdate()->first();
                if (!$newMedicine || $newMedicine->stock < $newQuantity) {
                    $availableStock = $newMedicine->stock ?? 0;
                    $errorMessage = str_replace(
                        [':medicineId', ':stock'], 
                        [$newMedicineId, $availableStock], 
                        Message::NOT_ENOUGH_STOCK_NEW_MEDICINE
                    );
                    throw ValidationException::withMessages([
                        'quantity' => $errorMessage
                    ]);
                }
                $newMedicine->decrement('stock', $newQuantity);
            } 
            // Case 2: Same medicine, only quantity changed
            elseif ($newQuantity != $item->quantity) {
                $delta = $newQuantity - $item->quantity;
                $medicine = Medicine::where('id', $item->medicine_id)->lockForUpdate()->first();

                if ($medicine) {
                    if ($delta > 0) {
                        // Need more stock
                        if ($medicine->stock < $delta) {
                            $errorMessage = str_replace(
                                [':medicineId', ':stock'], 
                                [$item->medicine_id, $medicine->stock], 
                                Message::NOT_ENOUGH_STOCK_MEDICINE
                            );
                            throw ValidationException::withMessages([
                                'quantity' => $errorMessage
                            ]);
                        }
                        $medicine->decrement('stock', $delta);
                    } else {
                        // Restore surplus stock
                        $medicine->increment('stock', abs($delta));
                    }
                }
            }

            // Update the item with new data
            $item->update($data);

            return $item;
        });
    }

    /**
     * Remove an item from the prescription and completely restore its stock.
     */
    public function removePrescriptionItem(PrescriptionItem $item)
    {
        return DB::transaction(function () use ($item) {
            // Lock the medicine row
            $medicine = Medicine::where('id', $item->medicine_id)->lockForUpdate()->first();

            if ($medicine) {
                // Restore the stock completely
                $medicine->increment('stock', $item->quantity);
            }

            // Delete the item
            $item->delete();
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
            $availableStock = $medicine->stock ?? 0;
            $errorMessage = str_replace(
                [':medicineId', ':stock'], 
                [$itemData['medicine_id'], $availableStock], 
                Message::NOT_ENOUGH_STOCK_MEDICINE
            );
            throw ValidationException::withMessages([
                'medicine_id' => $errorMessage
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

    public function updatePrescription(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $prescription = Prescription::with('items')->findOrFail($id);
            
            $prescription->update([
                'examination_id' => $data['examination_id'],
                'doctor_id' => $data['doctor_id'],
                'notes' => $data['notes'] ?? $prescription->notes,
            ]);

            if (isset($data['items'])) {
                $incomingItems = collect($data['items']);
                $incomingMedicineIds = $incomingItems->pluck('medicine_id')->toArray();
                foreach ($prescription->items as $existingItem) {
                    if (!in_array($existingItem->medicine_id, $incomingMedicineIds)) {
                        $this->removePrescriptionItem($existingItem);
                    }
                }

                foreach ($incomingItems as $itemData) {
                    $existingItem = $prescription->items()->where('medicine_id', $itemData['medicine_id'])->first();

                    if ($existingItem) {
                        $this->updatePrescriptionItem($existingItem, [
                            'quantity' => $itemData['quantity'],
                            'dosage' => $itemData['dosage'],
                            'usage_instruction' => $itemData['usage_instruction'] ?? null,
                        ]);
                    } else {
                        $this->processMedicineDeduction($prescription, $itemData);
                    }
                }
            }

            return $prescription->load('items.medicine');
        });
    }
}