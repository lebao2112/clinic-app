<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentService
{
    public function getAppointments($request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('scheduled_at', 'desc')->paginate($request->per_page ?? 15);
    }

    public function createAppointment(array $data)
    {
        $data['status'] = 'scheduled';
        return Appointment::create($data);
    }

    public function findAppointmentById($id)
    {
        return Appointment::with(['patient', 'doctor'])->findOrFail($id);
    }

    public function updateAppointment(Appointment $appointment, array $data)
    {
        $appointment->update($data);
        return $appointment->load(['patient', 'doctor']);
    }

    public function deleteAppointment(Appointment $appointment)
    {
        return $appointment->delete();
    }
}