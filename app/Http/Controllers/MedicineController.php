<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Http\Resources\MedicineResource;
use App\Services\MedicineService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class MedicineController extends Controller
{
    use ApiResponse;

    protected MedicineService $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

    public function index(Request $request)
    {
        try {
            $medicines = $this->medicineService->getMedicines();
            
            return $this->successResponse(MedicineResource::collection($medicines), Message::SUCCESS, 200, [
                'total'        => $medicines->total(),
                'current_page' => $medicines->currentPage(),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function store(StoreMedicineRequest $request)
    {
        try {
            $medicine = $this->medicineService->createMedicine($request->validated());
            
            return $this->successResponse(new MedicineResource($medicine), Message::SUCCESS, 201);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function show(int $id)
    {
        try {
            $medicine = $this->medicineService->findMedicineById($id);
            
            return $this->successResponse(new MedicineResource($medicine), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function update(UpdateMedicineRequest $request, int $id)
    {
        try {
            $medicine = $this->medicineService->findMedicineById($id);
            $updatedMedicine = $this->medicineService->updateMedicine($medicine, $request->validated());
            
            return $this->successResponse(new MedicineResource($updatedMedicine), Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $medicine = $this->medicineService->findMedicineById($id);
            $this->medicineService->deleteMedicine($medicine);
            
            return $this->successResponse(null, Message::SUCCESS);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(Message::NOT_FOUND, 404);
        } catch (Exception $e) {
            return $this->errorResponse(Message::INTERNAL_SERVER_ERROR, 500);
        }
    }
}