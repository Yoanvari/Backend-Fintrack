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

        $branches = Branch::all();
        foreach ($branches as $branch) {
            for ($i = 1; $i <= 5; $i++) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
                $fullName = $firstName . ' ' . $lastName;
                
                User::create([
                    'branch_id' => $branch->id,
                    'name' => $fullName,
                    'email' => strtolower($firstName . $i . '@sportcenter.com'),
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
