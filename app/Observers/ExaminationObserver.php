<?php

namespace App\Observers;

use App\Models\Examination;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ExaminationObserver
{
    /**
     * Handle the Examination "created" event.
     */
    public function created(Examination $examination): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'examination',
            'subject_id' => $examination->id,
            'action' => 'created',
            'meta' => [
                'appointment_id' => $examination->appointment_id,
                'doctor_id' => $examination->doctor_id,
                'patient_id' => $examination->patient_id,
                'diagnosis' => $examination->diagnosis
            ]
        ]);
    }
}