<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Appointment;
use App\Models\Examination;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicDemoWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed specialty data
        $specialty = Specialty::firstOrCreate(
            ['name' => 'Cardiology'],
            ['description' => 'Specialty dealing with disorders of the heart.']
        );

        // 2. Seed user and doctor records
        $user = User::firstOrCreate(
            ['email' => 'doctor.demo@clinic.com'],
            [
                'name' => 'Dr. Demo',
                'password' => Hash::make('password123'),
                'role_id' => 3, // Assuming 3 is the doctor role ID
            ]
        );

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'specialty_id' => $specialty->id,
                'license_number' => 'MED-' . rand(100000, 999999),
                'bio' => 'Experienced cardiologist with 10 years of practice.',
            ]
        );

        // 3. Seed patient records
        $patient = Patient::firstOrCreate(
            ['phone' => '0901234567'],
            [
                'code' => 'BN-' . rand(100000, 999999),
                'full_name' => 'John Doe Demo',
                'gender' => 'male',
                'date_of_birth' => '1998-05-15',
                'email' => 'patient.demo@example.com',
                'address' => 'Hanoi, Vietnam',
            ]
        );

        // 4. Seed medicine catalog
        $medicine = Medicine::firstOrCreate(
            ['code' => 'MED-DEMO'],
            [
                'name' => 'Demo Paracetamol',
                'unit' => 'Tablet',
                'price' => 10000,
                'stock' => 100,
                'is_active' => true,
            ]
        );

        // 5. Seed end-to-end workflow: Appointment -> Examination -> Prescription -> PrescriptionItem -> Invoice

        // Create appointment record
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->addHours(1),
            'status' => 'completed',
            'reason' => 'Routine check-up and mild chest pain.',
        ]);

        // Create examination record
        $examination = Examination::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'diagnosis' => 'Mild fatigue and elevated heart rate.',
            'notes' => 'Patient needs proper rest and hydration.',
            'examined_at' => now(),
        ]);

        // Create prescription record
        $prescription = Prescription::create([
            'examination_id' => $examination->id,
            'doctor_id' => $doctor->id,
            'notes' => 'Take medication after meals.',
        ]);

        // Create prescription items details
        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicine->id,
            'quantity' => 10,
            'dosage' => '2 tablets/day',
            'usage_instruction' => 'Take after breakfast and dinner',
        ]);

        // Create invoice record
        Invoice::create([
            'examination_id' => $examination->id,
            'invoice_code' => 'INV-' . strtoupper(Str::random(8)),
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);

        $this->command->info(' Clinic Demo Workflow Seeder executed successfully!');
    }
}