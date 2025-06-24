<?php

namespace Database\Factories;

use App\Models\BudgetDetail;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetDetail>
 */
class BudgetDetailFactory extends Factory
{
    protected $model = BudgetDetail::class;

    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'category_id' => Category::factory(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
        ];
    }
}
