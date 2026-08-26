<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'appointment',
            'subject_id' => $appointment->id,
            'action' => 'created',
            'meta' => [
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'scheduled_at' => $appointment->scheduled_at,
                'status' => $appointment->status
            ]
        ]);
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->isDirty('status')) {
            ActivityLog::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'subject_type' => 'appointment',
                'subject_id' => $appointment->id,
                'action' => 'status_changed',
                'meta' => [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'before' => ['status' => $appointment->getOriginal('status')],
                    'after' => ['status' => $appointment->status],
                ]
            ]);
        }
    }
}