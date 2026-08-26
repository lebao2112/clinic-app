<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     * Logs when a new patient is registered in the clinic system.
     */
    public function created(Patient $patient): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'patient',
            'subject_id' => $patient->id,
            'action' => 'created',
            'meta' => [
                'patient_code' => $patient->code,
                'full_name' => $patient->full_name,
                'phone' => $patient->phone,
            ]
        ]);
    }
}