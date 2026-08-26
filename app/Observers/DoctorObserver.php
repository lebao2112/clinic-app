<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DoctorObserver
{
    /**
     * Handle the Doctor "created" event.
     * Logs when a new doctor is onboarded into the system.
     */
    public function created(Doctor $doctor): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'doctor',
            'subject_id' => $doctor->id,
            'action' => 'created',
            'meta' => [
                'user_id' => $doctor->user_id, 
                'license_number' => $doctor->license_number,
                'specialty_id' => $doctor->specialty_id
            ]
        ]);
    }
}