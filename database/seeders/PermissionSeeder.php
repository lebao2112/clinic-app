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
        // Add more permissions according to the project requirements here
        $permissionsMap = [
            // Patients group
            'PATIENTS.INDEX'   => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            'PATIENTS.CREATE'  => ['ADMIN', 'RECEPTIONIST'],
            'PATIENTS.UPDATE'  => ['ADMIN', 'RECEPTIONIST'],
            'PATIENTS.SHOW'    => ['ADMIN', 'RECEPTIONIST', 'DOCTOR'],
            
            // Payments group
            'PAYMENTS.INDEX'   => ['ADMIN', 'CASHIER'],
            'PAYMENTS.CAPTURE' => ['ADMIN', 'CASHIER'],
            
            // Prescriptions group
            'PRESCRIPTIONS.CREATE'   => ['ADMIN', 'DOCTOR'],
            'PRESCRIPTIONS.DISPENSE' => ['ADMIN', 'PHARMACIST'],
        ];

        // 3. Iterate through the map to create Permissions and map them to Role_Permissions
        foreach ($permissionsMap as $permissionName => $roleNames) {
            // Create Permission (if it does not exist)
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
            ]);

            // Assign the permission to the specified Roles
            foreach ($roleNames as $roleName) {
                if (isset($roles[$roleName]) && $roles[$roleName]) {
                    // Use syncWithoutDetaching to prevent duplicate data
                    $roles[$roleName]->permissions()->syncWithoutDetaching([$permission->id]);
                }
            }
        }
    }
}