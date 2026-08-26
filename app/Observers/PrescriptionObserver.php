<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PrescriptionObserver
{
    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription): void
    {
        ActivityLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'subject_type' => 'prescription',
            'subject_id' => $prescription->id,
            'action' => 'prescribed',
            'meta' => [
                'examination_id' => $prescription->examination_id,
                'doctor_id' => $prescription->doctor_id,
                'notes' => $prescription->notes
            ]
        ]);
    }
}