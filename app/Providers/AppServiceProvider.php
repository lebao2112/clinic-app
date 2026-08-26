<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Examination;
use App\Models\Prescription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Medicine;

use App\Observers\UserObserver;
use App\Observers\PatientObserver;
use App\Observers\DoctorObserver;
use App\Observers\AppointmentObserver;
use App\Observers\ExaminationObserver;
use App\Observers\PrescriptionObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\MedicineObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user'         => 'App\Models\User',
            'appointment'  => 'App\Models\Appointment',
            'examination'  => 'App\Models\Examination',
            'prescription' => 'App\Models\Prescription',
            'invoice'      => 'App\Models\Invoice',
            'payment'      => 'App\Models\Payment',
            'medicine'     => 'App\Models\Medicine',
            'patient'      => 'App\Models\Patient',
            'doctor'       => 'App\Models\Doctor',
        ]);

        User::observe(UserObserver::class);
        Patient::observe(PatientObserver::class);
        Doctor::observe(DoctorObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Examination::observe(ExaminationObserver::class);
        Prescription::observe(PrescriptionObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        Medicine::observe(MedicineObserver::class);
    }
}