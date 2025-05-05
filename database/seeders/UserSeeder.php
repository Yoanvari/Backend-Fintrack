<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::Create([
            'branch_id' => 1,
            'name' => 'Super Admin',
            'email' => 'superadmin@fintrack.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin'
        ]);
    }
}
