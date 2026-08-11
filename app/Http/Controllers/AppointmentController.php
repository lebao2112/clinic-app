<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AppointmentController extends Controller implements HasMiddleware
{
    use ApiResponse;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:APPOINTMENTS.VIEW', only: ['index', 'show']),
            new Middleware('permission:APPOINTMENTS.CREATE', only: ['store']),
            new Middleware('permission:APPOINTMENTS.UPDATE', only: ['update']),
            new Middleware('permission:APPOINTMENTS.DELETE', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.user']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('doctor_id') && !empty($request->doctor_id)) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('scheduled_at', 'desc')->paginate($request->per_page ?? 15);

        return $this->successResponse($appointments->items(), Message::SUCCESS, 200, [
            'current_page' => $appointments->currentPage(),
            'last_page'    => $appointments->lastPage(),
            'total'        => $appointments->total(),
        ]);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        
        $data['status'] = 'scheduled';

        $appointment = Appointment::create($data);

        return $this->successResponse($appointment, Message::SUCCESS, 201);
    }

    public function show(string $id)
    {
        $appointment = Appointment::with(['patient', 'doctor.user'])->find($id);

        if (!$appointment) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        return $this->successResponse($appointment);
    }

    public function update(UpdateAppointmentRequest $request, string $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        $appointment->update($request->validated());

        return $this->successResponse($appointment);
    }

    public function destroy(string $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        $appointment->delete();

        return $this->successResponse(null, Message::SUCCESS);
    }
}