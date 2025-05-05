<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_name' => $this->faker->company(),
            'branch_code' => $this->faker->unique()->bothify('BR-#####'),
            'branch_address' => $this->faker->address(),
        ];
    }
}
