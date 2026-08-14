<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Examination;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExaminationService
{
    /**
     * Retrieve a paginated list of examinations.
     */
    public function getExaminations(Request $request)
    {
        return Examination::with(['appointment', 'doctor', 'patient'])->paginate(15);
    }

    /**
     * Find an examination by its ID or throw an exception.
     */
    public function findExaminationById(int $id): Examination
    {
        return Examination::with(['appointment', 'doctor', 'patient'])->findOrFail($id);
    }

    /**
     * Create an examination and complete the appointment atomically within a transaction.
     */
    public function createExamination(array $data): Examination
    {
        return DB::transaction(function () use ($data) {
            // 1. Lock the appointment to prevent concurrent modifications
            $appointment = Appointment::lockForUpdate()->findOrFail($data['appointment_id']);

            // 2. Validate specific invalid statuses for clear business messages
            if ($appointment->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'appointment_id' => ['Cannot create an examination for a cancelled appointment.']
                ]);
            }

            if ($appointment->status === 'completed') {
                throw ValidationException::withMessages([
                    'appointment_id' => ['This appointment has already been completed.']
                ]);
            }

            // General check to ensure only 'confirmed' appointments proceed
            if ($appointment->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'appointment_id' => ['Examinations can only be created for confirmed appointments.']
                ]);
            }

            // 3. Prevent duplicate examinations
            if (Examination::where('appointment_id', $appointment->id)->exists()) {
                throw ValidationException::withMessages([
                    'appointment_id' => ['An examination record already exists for this appointment.']
                ]);
            }

            // 4. Create the examination record
            $examinationData = [
                'appointment_id' => $appointment->id,
                'doctor_id'      => $appointment->doctor_id,
                'patient_id'     => $appointment->patient_id,
                'diagnosis'      => $data['diagnosis'],
                'notes'          => $data['notes'] ?? null,
                'examined_at'    => now(),
            ];

            $examination = Examination::create($examinationData);

            // 5. Update appointment status to 'completed' automatically
            $appointment->update(['status' => 'completed']);

            return $examination;
        });
    }

    /**
     * Update an existing examination record.
     */
    public function updateExamination(Examination $examination, array $data): Examination
    {
        $examination->update($data);
        return $examination->fresh(['appointment', 'doctor', 'patient']);
    }

    /**
     * Delete an examination record.
     */
    public function deleteExamination(Examination $examination): bool
    {
        return $examination->delete();
    }
}