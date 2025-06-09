<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetDetail;
use App\Models\Budget;
use App\Models\Category;
use Faker\Factory as Faker;

class BudgetDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $budgets = Budget::all();
        $categories = Category::where('category_type', 'pengeluaran')->get();

        foreach ($budgets as $budget) {
            $usedCategories = $categories->random(min(4, $categories->count()));

            foreach ($usedCategories as $category) {
                BudgetDetail::create([
                    'budget_id' => $budget->id,
                    'category_id' => $category->id,
                    'description' => $faker->sentence(),
                    'amount' => $faker->randomFloat(2, 100000, 10000000),
                ]);
            }
        }
    }
}
