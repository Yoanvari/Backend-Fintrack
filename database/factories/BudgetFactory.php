<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'period' => $this->faker->date(),
            'submission_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['draf', 'diajukan', 'disetujui', 'ditolak', 'revisi']),
            'revision_note' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }
}
