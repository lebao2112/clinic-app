<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Examination;
use App\Models\Prescription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Medicine;
use App\Models\User;

use App\Constants\Message;
use App\Traits\ApiResponse;

class StatsController extends Controller
{
    use ApiResponse;

    /**
     * Retrieve general clinic statistics for the dashboard using PostgreSQL aggregates.
     * Permission required: STATS.SHOW
     */
    public function index(Request $request)
    {
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $appointmentsToday = Appointment::whereRaw('scheduled_at::date = CURRENT_DATE')->count();
        $totalExaminations = Examination::count();
        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereNotNull('paid_at')
            ->whereRaw('EXTRACT(MONTH FROM paid_at) = EXTRACT(MONTH FROM CURRENT_DATE)')
            ->whereRaw('EXTRACT(YEAR FROM paid_at) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->sum('amount');
        $lowStockThreshold = 50;
        $lowStockMedicines = Medicine::where('is_active', true)
            ->where('stock', '<', $lowStockThreshold)
            ->count();
        $data = [
            'total_patients'      => $totalPatients,
            'total_doctors'       => $totalDoctors,
            'appointments_today'  => $appointmentsToday,
            'total_examinations'  => $totalExaminations,
            'monthly_revenue'     => (float) $monthlyRevenue,
            'low_stock_medicines' => $lowStockMedicines,
        ];
        return $this->successResponse($data, Message::SUCCESS);
    }
}