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
        $users = User::where('role', 'admin')->get();

        foreach ($branches as $branch) {
            // Anggaran Tahunan (diupdate dengan nilai dan tanggal lebih realistis)
            MasterBudget::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => 'Anggaran Tahunan ' . Carbon::now()->year
                ],
                [
                    'user_id' => $users->random()->id, // Random admin
                    'total_amount' => $faker->numberBetween(2000000000, 5000000000), // Nilai acak antara 2-5 Miliar
                    'start_date' => Carbon::create(Carbon::now()->year, 1, 1)->startOfDay(),
                    'end_date' => Carbon::create(Carbon::now()->year, 12, 31)->endOfDay(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Anggaran Bulanan (untuk 12 bulan dalam tahun berjalan)
            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::create(Carbon::now()->year, $month, 1);
                
                MasterBudget::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => 'Anggaran Bulanan ' . $date->format('F Y')
                    ],
                    [
                        'user_id' => $users->random()->id,
                        'total_amount' => $faker->numberBetween(150000000, 300000000), // 150-300 Juta
                        'start_date' => $date->copy()->startOfMonth(),
                        'end_date' => $date->copy()->endOfMonth(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
