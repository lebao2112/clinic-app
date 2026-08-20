<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB; // <-- Thêm thư viện DB
use Tests\TestCase;

class Week2IntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        putenv('DB_DATABASE=laravel_testing');
        $_ENV['DB_DATABASE'] = 'laravel_testing';
        $_SERVER['DB_DATABASE'] = 'laravel_testing';

        parent::setUp();

        config(['database.connections.pgsql.database' => 'laravel_testing']);
        DB::purge('pgsql');
        DB::reconnect('pgsql');

        $this->app['auth']->forgetGuards();

        Gate::before(function ($user, $ability) {
            if ($user && $user->role) {
                return $user->role->permissions->contains('name', $ability);
            }
            return false;
        });
    }

    /**
     * Test successful user login.
     */
    public function test_user_can_login_successfully(): void  
    {
        $role = Role::create(['name' => 'Default Role ' . uniqid()]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'testuser_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test user without permission receives 403 Forbidden.
     */
    public function test_user_without_permission_receives_403(): void
    {
        $user = User::factory()->create([
            'role_id' => null
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/patients', [
            'full_name' => 'Unauthorized Patient',
            'gender' => 'male',
            'date_of_birth' => '2000-01-01',
            'phone' => '0123456789',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test authorized user can create a patient.
     */
    public function test_authorized_user_can_create_patient(): void
    {
        $role = Role::create(['name' => 'Admin Role ' . uniqid()]);
        
        $permissionNames = [
            'PATIENTS.STORE',
            'PATIENTS.CREATE'
        ];

        foreach ($permissionNames as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName]);
            $role->permissions()->attach($permission->id);
        }

        $admin = User::factory()->create([
            'role_id' => $role->id
        ]);

        $admin->load('role.permissions');
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/patients', [
            'full_name' => 'Valid Patient',
            'phone' => '0123456789',
            'address' => 'Hanoi, Vietnam',
            'date_of_birth' => '2000-01-01', 
            'gender' => 'male',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', [
            'phone' => '0123456789'
        ]);
    }

    /**
     * Test authorized user can create an appointment.
     */
    public function test_authorized_user_can_create_appointment(): void
    {
        $role = Role::create(['name' => 'Admin Role ' . uniqid()]);
        
        $permissionNames = [
            'APPOINTMENTS.STORE',
            'APPOINTMENTS.CREATE',
            'DOCTORS.FINDALL',
            'DOCTORS.FINDONE',
            'PATIENTS.FINDALL',
            'PATIENTS.FINDONE',
            'PATIENTS.STORE',
            'PATIENTS.CREATE'
        ];

        foreach ($permissionNames as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName]);
            $role->permissions()->attach($permission->id);
        }

        $admin = User::factory()->create([
            'role_id' => $role->id
        ]);

        $admin->load('role.permissions');
        $this->actingAs($admin, 'sanctum');

        $specialty = Specialty::forceCreate([
            'name' => 'General Internal Medicine ' . uniqid()
        ]);

        $doctorUser = User::factory()->create(['role_id' => $role->id]);
        $doctor = Doctor::forceCreate([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->id,
            'license_number' => 'DOC-' . uniqid()
        ]);

        $patientService = app(\App\Services\PatientService::class);
        $patient = $patientService->createPatient([
            'full_name' => 'Dummy Patient',
            'phone' => '09' . mt_rand(10000000, 99999999),
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'address' => 'Hanoi'
        ]);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => 'Headache and dizziness',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);
    }
}