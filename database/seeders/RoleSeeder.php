<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Define the 5 default roles required by the system
        $roles = [
            ['name' => 'ADMIN', 'display_name' => 'Administrator'],
            ['name' => 'RECEPTIONIST', 'display_name' => 'Receptionist'],
            ['name' => 'DOCTOR', 'display_name' => 'Doctor'],
            ['name' => 'PHARMACIST', 'display_name' => 'Pharmacist'],
            ['name' => 'CASHIER', 'display_name' => 'Cashier'],
        ];

        // Use updateOrCreate to prevent duplicate entries when running seeder multiple times
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']], 
                ['display_name' => $role['display_name']]
            );
        }
    }
}