<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Examination;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ExaminationService
{
    /**
     * Create an examination based on a confirmed appointment.
     */
    public function createExamination(array $data): Examination
    {
        return DB::transaction(function () use ($data) {
            // Retrieve and lock the appointment to prevent concurrent modifications
            $appointment = Appointment::lockForUpdate()->findOrFail($data['appointment_id']);

            // Validate that the appointment status is 'confirmed'
            if ($appointment->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'appointment_id' => ['Examinations can only be created for confirmed appointments.'],
                ]);
            }

            // Ensure only one examination exists per appointment
            if (Examination::where('appointment_id', $appointment->id)->exists()) {
                throw ValidationException::withMessages([
                    'appointment_id' => ['An examination record already exists for this appointment.'],
                ]);
            }

            // Extract doctor_id and patient_id strictly from the appointment
            $examinationData = [
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'diagnosis' => $data['diagnosis'],
                'notes' => $data['notes'] ?? null,
                'examined_at' => now(),
            ];

            return Examination::create($examinationData);
        });
    }
}