<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Transaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;
    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_locked' => false,
        ];
    }
}
