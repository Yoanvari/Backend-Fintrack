<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\Branch;
use App\Models\User;
use Faker\Factory as Faker;


class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $branches = Branch::all();
        $statuses = ['draf', 'diajukan', 'disetujui', 'ditolak', 'revisi'];

        foreach ($branches as $branch) {
            $users = User::where('branch_id', $branch->id)->get();

            foreach ($users as $user) {
                for ($i = 0; $i < 2; $i++) {
                    Budget::create([
                        'branch_id' => $branch->id,
                        'user_id' => $user->id,
                        'period' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                        'submission_date' => $faker->dateTimeBetween('-5 months', 'now'),
                        'status' => $faker->randomElement($statuses),
                        'revision_note' => $faker->optional()->sentence(),
                    ]);
                }
            }
        }
    }
}
