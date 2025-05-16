<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Super Admin
        User::create([
            'branch_id' => Branch::first()->id,
            'name' => 'Super Admin',
            'email' => 'superadmin@sportcenter.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Branch Managers
        $branches = Branch::all();
        foreach ($branches as $branch) {
            User::create([
                'branch_id' => $branch->id,
                'name' => 'Manager ' . $branch->branch_name,
                'email' => strtolower(str_replace(' ', '', 'manager_'.$branch->branch_name)).'@sportcenter.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        // Staff (5 staff per branch)
        $staffRoles = ['Front Office', 'Fitness Trainer', 'Customer Service', 'Finance Staff', 'Maintenance'];
        
        foreach ($branches as $branch) {
            foreach ($staffRoles as $role) {
                User::create([
                    'branch_id' => $branch->id,
                    'name' => $role . ' ' . $branch->branch_name,
                    'email' => strtolower(str_replace(' ', '', $role.'_'.$branch->branch_name)).'@sportcenter.com',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
