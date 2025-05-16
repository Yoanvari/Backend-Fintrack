<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterBudget;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class MasterBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $branches = Branch::all();
        $manager = User::where('name', 'like', 'Manager%')->first();

        foreach ($branches as $branch) {
            // Annual Budget
            MasterBudget::create([
                'branch_id' => $branch->id,
                'user_id' => $manager->id,
                'name' => 'Anggaran Tahunan ' . $branch->branch_name,
                'total_amount' => 2000000000,
                'start_date' => Carbon::now()->startOfYear(),
                'end_date' => Carbon::now()->endOfYear(),
            ]);

            // Monthly Budget
            MasterBudget::create([
                'branch_id' => $branch->id,
                'user_id' => $manager->id,
                'name' => 'Anggaran Bulanan ' . Carbon::now()->format('F Y'),
                'total_amount' => 200000000,
                'start_date' => Carbon::now()->startOfMonth(),
                'end_date' => Carbon::now()->endOfMonth(),
            ]);
        }
    }
}
