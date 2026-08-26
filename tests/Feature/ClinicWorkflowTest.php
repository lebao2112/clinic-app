<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Appointment;

class ClinicWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_without_permission_receives_forbidden_on_stats(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/stats');
        $response->assertStatus(403);
    }

    public function test_receptionist_cannot_create_invoice(): void
    {
        $role = Role::firstOrCreate(['name' => 'RECEPTIONIST']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/invoices', [
            'examination_id' => 1,
            'subtotal' => 500000,
            'discount' => 0,
            'total' => 500000
        ]);
        
        $response->assertStatus(403);
    }

    public function test_doctor_cannot_capture_payment(): void
    {
        $role = Role::firstOrCreate(['name' => 'DOCTOR']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/payments/1/capture');
        
        $response->assertStatus(403);
    }

    public function test_core_workflow_patient_to_appointment_to_examination(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsurePermission::class]);

        Gate::before(function () {
            return true;
        });

        $role = Role::firstOrCreate(['name' => 'ADMIN']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $patientResponse = $this->actingAs($user, 'sanctum')->postJson('/api/patients', [
            'code' => 'BN-99999',
            'full_name' => 'Nguyen Van Test',
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'phone' => '0901234567',
            'address' => 'Hanoi'
        ]);
        
        $patientResponse->assertStatus(201);
        $patientId = $patientResponse->json('data.id') ?? Patient::latest()->first()->id;
        $specialty = Specialty::create([
            'name' => 'Test Specialty',
            'description' => 'Test description'
        ]);
        
        $doctorUser = User::factory()->create();
        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->id,
            'license_number' => 'CCHN-TEST',
            'bio' => 'Test bio'
        ]);
        $appointmentResponse = $this->actingAs($user, 'sanctum')->postJson('/api/appointments', [
            'patient_id' => $patientId,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'reason' => 'Dau dau'
        ]);

        $appointmentResponse->assertStatus(201);
        $appointmentId = $appointmentResponse->json('data.id') ?? Appointment::latest()->first()->id;
        $appointment = Appointment::find($appointmentId);
        $appointment->update(['status' => 'confirmed']);
        $examinationResponse = $this->actingAs($user, 'sanctum')->postJson('/api/examinations', [
            'appointment_id' => $appointmentId,
            'doctor_id' => $doctor->id,
            'patient_id' => $patientId,
            'diagnosis' => 'Viem hong cap',
            'notes' => 'Uong nhieu nuoc am'
        ]);

        $examinationResponse->assertStatus(201);
    }
}