<?php

namespace Database\Factories;

use App\Models\MasterBudget;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterBudgetFactory extends Factory
{
    protected $model = MasterBudget::class;

    public function definition(): array
    {
        $startDate = $this->faker->date();
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year')->format('Y-m-d');

        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'total_amount' => $this->faker->numberBetween(1000, 10000),
            // 'year' => $this->faker->year(),
            // 'description' => $this->faker->sentence(),
            // 'status' => $this->faker->randomElement(['draft', 'approved', 'rejected']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
