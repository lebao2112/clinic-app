<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Routing\Controllers\HasMiddleware; 
use Illuminate\Routing\Controllers\Middleware;    

class PatientController extends Controller implements HasMiddleware 
{
    use ApiResponse;

    public static function middleware(): array
    {
        return [
            new Middleware('role:RECEPTIONIST|DOCTOR|ADMIN', only: ['store', 'update', 'destroy']),
            new Middleware('role:RECEPTIONIST|DOCTOR|CASHIER|ADMIN', only: ['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $patients = $query->latest()->paginate($request->per_page ?? 15);

        return $this->successResponse($patients->items(), Message::SUCCESS, 200, [
            'current_page' => $patients->currentPage(),
            'last_page'    => $patients->lastPage(),
            'total'        => $patients->total(),
        ]);
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        $maxId = Patient::withTrashed()->max('id') ?? 0;
        $data['code'] = 'BN-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);

        $patient = Patient::create($data);

        return $this->successResponse($patient, Message::SUCCESS, 201);
    }

    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        return $this->successResponse($patient);
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        $patient->update($request->validated());

        return $this->successResponse($patient);
    }

    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        }

        $patient->delete();

        return $this->successResponse(null, Message::SUCCESS);
    }
}