<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch the ADMIN role from the database
        $adminRole = Role::where('name', 'ADMIN')->first();

        // 2. Ensure the role exists before creating the user
        if ($adminRole) {
            // 3. Create the first Admin user with the required credentials
            // Using updateOrCreate prevents duplicate entries if the seeder runs multiple times
            User::updateOrCreate(
                ['email' => 'admin@clinic.test'], // Specific email from requirements
                [
                    'name'     => 'System Admin',
                    'password' => Hash::make('password'), // Hash the default password (e.g., 'password')
                    'role_id'  => $adminRole->id,
                ]
            );
        }
    }
}