<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Faker\Factory as Faker;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Seed Branches (Cabang Sport Center)
        $branches = [
            [
                'branch_code' => 'SCJKT',
                'branch_name' => 'Sport Center Jakarta',
                'branch_address' => 'Jl. Sudirman No. 1, Jakarta'
            ],
            [
                'branch_code' => 'SCBDG',
                'branch_name' => 'Sport Center Bandung',
                'branch_address' => 'Jl. Dago No. 45, Bandung'
            ],
            [
                'branch_code' => 'SCSBY',
                'branch_name' => 'Sport Center Surabaya',
                'branch_address' => 'Jl. Tunjungan No. 12, Surabaya'
            ],
            [
                'branch_code' => 'SCBLI',
                'branch_name' => 'Sport Center Bali',
                'branch_address' => 'Jl. Kuta Beach No. 8, Bali'
            ]
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
