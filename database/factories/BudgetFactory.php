<?php

namespace Database\Factories;

use App\Models\MasterBudget;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/BudgetFactory.php
public function definition(): array
{
    return [
        'master_budget_id' => MasterBudget::factory(),
        'user_id' => User::factory(),
        'category_id' => Category::factory(),
        'name' => $this->faker->word,
        'amount' => $this->faker->randomFloat(2, 100, 1000),
        'description' => $this->faker->sentence,
    ];
}

}
