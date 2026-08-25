<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Examination;
use App\Constants\Message;
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
        $query = Examination::with(['appointment', 'doctor.user', 'patient']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($patientQuery) use ($search) {
                    $patientQuery->where('full_name', 'ilike', '%' . $search . '%')
                                 ->orWhere('code', 'ilike', '%' . $search . '%');
                })
                ->orWhereHas('doctor.user', function($doctorQuery) use ($search) {
                    $doctorQuery->where('name', 'ilike', '%' . $search . '%');
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);
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
                    'appointment_id' => [Message::EXAMINATION_CANCELLED_APPOINTMENT]
                ]);
            }

            if ($appointment->status === 'completed') {
                throw ValidationException::withMessages([
                    'appointment_id' => [Message::EXAMINATION_ALREADY_COMPLETED]
                ]);
            }

            // General check to ensure only 'confirmed' appointments proceed
            if ($appointment->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'appointment_id' => [Message::EXAMINATION_CONFIRMED_REQUIRED]
                ]);
            }

            // 3. Prevent duplicate examinations
            if (Examination::where('appointment_id', $appointment->id)->exists()) {
                throw ValidationException::withMessages([
                    'appointment_id' => [Message::EXAMINATION_ALREADY_EXISTS]
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