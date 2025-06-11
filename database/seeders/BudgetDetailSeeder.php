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

        $descriptionsByCategory = [
            'Gaji' => ['Gaji staff tetap', 'Gaji instruktur freelance', 'Bonus bulanan'],
            'Operasional' => ['Pembelian alat tulis', 'Biaya listrik', 'Biaya air', 'Langganan internet'],
            'Pemeliharaan' => ['Servis alat gym', 'Penggantian suku cadang', 'Perawatan bulanan'],
            'Promosi' => ['Iklan media sosial', 'Pembuatan banner', 'Flyer dan brosur'],
            'Konsumsi dan Acara' => ['Snack acara bulanan', 'Konsumsi peserta event'],
            'Transportasi' => ['Transport staff ke event', 'Biaya bensin untuk pengiriman']
        ];

        foreach ($budgets as $budget) {
            $usedCategories = $categories->random(min(4, $categories->count()));

            foreach ($usedCategories as $category) {
                $descriptionOptions = $descriptionsByCategory[$category->category_name] ?? [$faker->sentence()];
                $description = $faker->randomElement($descriptionOptions);

                BudgetDetail::create([
                    'budget_id' => $budget->id,
                    'category_id' => $category->id,
                    'description' => $description,
                    'amount' => $faker->randomFloat(2, 100000, 10000000),
                ]);
            }
        }
    }
}
