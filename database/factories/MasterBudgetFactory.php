<?php

namespace Database\Factories;

use App\Models\MasterBudget;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterBudgetFactory extends Factory
{
    protected $model = MasterBudget::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => $this->faker->word(),
            'total_amount' => $this->faker->numberBetween(1000, 10000),
        ];
    }
}
