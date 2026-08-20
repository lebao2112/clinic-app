<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch all roles created in T1.7
        $roles = [
            'ADMIN'        => Role::where('name', 'ADMIN')->first(),
            'RECEPTIONIST' => Role::where('name', 'RECEPTIONIST')->first(),
            'DOCTOR'       => Role::where('name', 'DOCTOR')->first(),
            'PHARMACIST'   => Role::where('name', 'PHARMACIST')->first(),
            'CASHIER'      => Role::where('name', 'CASHIER')->first(),
        ];

        // 2. DEFINE PERMISSION MAP 
        $permissionsMap = [
            // Specialties
            'SPECIALTIES.FINDALL' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'SPECIALTIES.CREATE'  => ['ADMIN'],
            'SPECIALTIES.FINDONE' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'SPECIALTIES.UPDATE'  => ['ADMIN'],
            'SPECIALTIES.DELETE'  => ['ADMIN'],

            // Doctors
            'DOCTORS.FINDALL' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'DOCTORS.CREATE'  => ['ADMIN'],
            'DOCTORS.FINDONE' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'DOCTORS.UPDATE'  => ['ADMIN'],
            'DOCTORS.DELETE'  => ['ADMIN'],

            // Patients
            'PATIENTS.FINDALL' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'],
            'PATIENTS.CREATE'  => ['ADMIN', 'RECEPTIONIST'],
            'PATIENTS.FINDONE' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'],
            'PATIENTS.UPDATE'  => ['ADMIN', 'RECEPTIONIST'],
            'PATIENTS.DELETE'  => ['ADMIN'],
            
            // Appointments
            'APPOINTMENTS.FINDALL'     => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'],
            'APPOINTMENTS.CREATE'      => ['ADMIN', 'RECEPTIONIST'],
            'APPOINTMENTS.FINDONE'     => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'],
            'APPOINTMENTS.UPDATE'      => ['ADMIN', 'RECEPTIONIST'],
            'APPOINTMENTS.UPDATESTATUS'=> ['ADMIN', 'RECEPTIONIST'],

            // Examinations
            'EXAMINATIONS.FINDALL' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'EXAMINATIONS.CREATE'  => ['ADMIN', 'DOCTOR'],
            'EXAMINATIONS.FINDONE' => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'EXAMINATIONS.UPDATE'  => ['ADMIN', 'DOCTOR'],

            // Medicines
            'MEDICINES.FINDALL'      => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'PHARMACIST'],
            'MEDICINES.CREATE'       => ['ADMIN', 'PHARMACIST'],
            'MEDICINES.FINDONE'      => ['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'PHARMACIST'],
            'MEDICINES.UPDATE'       => ['ADMIN', 'PHARMACIST'],
            'MEDICINES.DELETE'       => ['ADMIN', 'PHARMACIST'],
            'MEDICINES.ADJUSTSTOCK'  => ['ADMIN', 'PHARMACIST'],

            // Prescriptions
            'PRESCRIPTIONS.FINDALL'   => ['ADMIN', 'DOCTOR', 'PHARMACIST'],
            'PRESCRIPTIONS.CREATE'    => ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.FINDONE'   => ['ADMIN', 'DOCTOR', 'PHARMACIST'],
            'PRESCRIPTIONS.UPDATE'    => ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.ADDITEM'   => ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.UPDATEITEM'=> ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.REMOVEITEM'=> ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.DISPENSE'  => ['ADMIN', 'PHARMACIST'],

            // Invoices
            'INVOICES.FINDALL'      => ['ADMIN', 'CASHIER'],
            'INVOICES.CREATE'       => ['ADMIN', 'CASHIER'],
            'INVOICES.FINDONE'      => ['ADMIN', 'CASHIER'],
            'INVOICES.UPDATE'       => ['ADMIN', 'CASHIER'],
            'INVOICES.UPDATESTATUS' => ['ADMIN', 'CASHIER'],

            // Payments
            'PAYMENTS.FINDALL'   => ['ADMIN', 'CASHIER'],
            'PAYMENTS.CREATE'    => ['ADMIN', 'CASHIER'],
            'PAYMENTS.CAPTURE'   => ['ADMIN', 'CASHIER'],

            // Stats
            'STATS.SHOW'         => ['ADMIN'],

            // Users & Roles
            'USERS.FINDALL'      => ['ADMIN'],
            'USERS.CREATE'       => ['ADMIN'],
            'USERS.FINDONE'      => ['ADMIN'],
            'USERS.UPDATE'       => ['ADMIN'],
            'USERS.DELETE'       => ['ADMIN'],
            'USERS.UPDATESTATUS' => ['ADMIN'],
            'ROLES.FINDALL'      => ['ADMIN'],
        ];

        // 3. Iterate through the map to create Permissions and map them to Role_Permissions
        foreach ($permissionsMap as $permissionName => $roleNames) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
            ]);

            foreach ($roleNames as $roleName) {
                if (isset($roles[$roleName]) && $roles[$roleName]) {
                    $roles[$roleName]->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
    }
}