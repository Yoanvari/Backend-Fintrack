<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::Create([
            'branch_code' => 'MLG01',
            'branch_name' => 'Sport Malang',
            'branch_address' => 'Malang, Lowokwaru'
        ]);
    }
}
