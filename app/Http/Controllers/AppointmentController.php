<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\ChangeAppointmentStatusRequest; 
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException; 
use Exception;

class AppointmentController extends Controller
{
    use ApiResponse;

    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        try {
            $appointments = $this->appointmentService->getAppointments($request);
            
            return $this->successResponse(AppointmentResource::collection($appointments), Message::SUCCESS, 200, [
                'total'        => $appointments->total(),
                'current_page' => $appointments->currentPage(),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function store(StoreAppointmentRequest $request)
    {
        try {
            $appointment = $this->appointmentService->createAppointment($request->validated());
            
            return $this->successResponse(
                new AppointmentResource($appointment->load(['patient', 'doctor'])), 
                Message::SUCCESS, 
                201
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => Message::VALIDATION_FAILED,
                'errors'  => [
                    'schedule_conflict' => [$e->getMessage()]
                ]
            ], 422);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function show(int $id)
    {
        try {
            $appointment = $this->appointmentService->findAppointmentById($id);
            return $this->successResponse(new AppointmentResource($appointment), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function update(UpdateAppointmentRequest $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->findAppointmentById($id);
            $updatedAppointment = $this->appointmentService->updateAppointment($appointment, $request->validated());
            
            return $this->successResponse(new AppointmentResource($updatedAppointment), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => Message::VALIDATION_FAILED,
                'errors'  => [
                    'schedule_conflict' => [$e->getMessage()]
                ]
            ], 422);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $appointment = $this->appointmentService->findAppointmentById($id);
            $this->appointmentService->deleteAppointment($appointment);
            
            return $this->successResponse(null, Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function changeStatus(ChangeAppointmentStatusRequest $request, int $id)
    {
        try {
            $appointment = $this->appointmentService->findAppointmentById($id);
            $updatedAppointment = $this->appointmentService->changeStatus($appointment, $request->status);
            
            return $this->successResponse(new AppointmentResource($updatedAppointment), Message::SUCCESS);
            
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
            
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse(Message::VALIDATION_FAILED . ': ' . $e->getMessage(), 422);
            
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
}