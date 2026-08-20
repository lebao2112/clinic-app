<?php

namespace App\Services;

use App\Models\Appointment;
use InvalidArgumentException;
use App\Constants\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentService
{
    public function getAppointments(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('scheduled_at', 'desc')->paginate($request->per_page ?? 15);
    }

    public function createAppointment(array $data)
    {
        // T2.6: Prevent doctor appointment overlap
        $this->checkDoctorAvailability($data['doctor_id'], $data['scheduled_at']);

        // Set default status to 'scheduled' for new appointments
        $data['status'] = 'scheduled';
        return Appointment::create($data);
    }

    public function findAppointmentById(int $id)
    {
        return Appointment::with(['patient', 'doctor'])->findOrFail($id);
    }

    public function updateAppointment(Appointment $appointment, array $data)
    {
        // T2.6: Determine the doctor and time to check (fallback to existing if not updated)
        $doctorId = $data['doctor_id'] ?? $appointment->doctor_id;
        $scheduledAt = $data['scheduled_at'] ?? $appointment->scheduled_at;

        // Only check for overlap if doctor_id or scheduled_at is being modified
        if (isset($data['doctor_id']) || isset($data['scheduled_at'])) {
            $this->checkDoctorAvailability($doctorId, $scheduledAt, $appointment->id);
        }

        $appointment->update($data);
        return $appointment->load(['patient', 'doctor']);
    }

    public function deleteAppointment(Appointment $appointment)
    {
        return $appointment->delete();
    }

    // T2.5: State Machine for appointment status transitions
    public function changeStatus(Appointment $appointment, string $newStatus)
    {
        $validTransitions = [
            'scheduled' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'cancelled' => [], 
            'completed' => [], 
        ];

        $currentStatus = $appointment->status;

        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new InvalidArgumentException("State transition not allowed from {$currentStatus} to {$newStatus}.");
        }

        $appointment->update(['status' => $newStatus]);
        
        return $appointment->load(['patient', 'doctor']);
    }

    /**
     * T2.6: Check if the doctor is already booked at the given time block.
     * Excludes 'cancelled' appointments.
     */
    protected function checkDoctorAvailability(int $doctorId, string $scheduledAt, ?int $ignoreAppointmentId = null)
    {
        // Assuming each appointment takes 30 minutes
        $appointmentDuration = 30; 
        
        $requestedTime = Carbon::parse($scheduledAt);
        // Create a time block to check for overlapping appointments
        $startTimeBlock = $requestedTime->copy()->subMinutes($appointmentDuration - 1)->toDateTimeString();
        $endTimeBlock = $requestedTime->copy()->addMinutes($appointmentDuration - 1)->toDateTimeString();

        $query = Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('scheduled_at', [$startTimeBlock, $endTimeBlock]);

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        // Throw InvalidArgumentException so the Controller can catch and return a clean 422 response
        if ($query->exists()) {
            throw new InvalidArgumentException("The doctor is busy around this time. Please choose a slot at least {$appointmentDuration} minutes apart.");
        }
    }
}