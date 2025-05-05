<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\MasterBudget;
use App\Models\Category;
use Faker\Factory as Faker;

class BudgetSeeder extends Seeder
{
    public function run()
    {
        $masterBudgets = MasterBudget::all();
        $expenseCategories = Category::where('category_type', 'expense')->get();

        foreach ($masterBudgets as $masterBudget) {
            $totalPercentage = 0;
            
            foreach ($expenseCategories as $category) {
                $percentage = $this->getBudgetPercentage($category->category_name);
                $amount = $masterBudget->total_amount * ($percentage / 100);
                
                Budget::create([
                    'master_budget_id' => $masterBudget->id,
                    'user_id' => $masterBudget->user_id,
                    'category_id' => $category->id,
                    'name' => 'Anggaran ' . $category->category_name . ' ' . $masterBudget->name,
                    'amount' => $amount,
                    'description' => 'Alokasi ' . $percentage . '% dari total anggaran'
                ]);
                
                $totalPercentage += $percentage;
            }
        }
    }

    private function getBudgetPercentage($categoryName)
    {
        $percentages = [
            'Gaji Staff' => 35,
            'Gaji Instruktur' => 25,
            'Pemeliharaan Peralatan' => 15,
            'Listrik dan Air' => 10,
            'Promosi' => 10,
            'default' => 5
        ];

        return $percentages[$categoryName] ?? $percentages['default'];
    }
}
