<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetDetail;
use App\Models\Budget;
use App\Models\Transaction;
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

        foreach ($budgets as $budget) {
            $transactions = Transaction::where('category_id', $budget->category_id)
                ->where('branch_id', $budget->masterBudget->branch_id)
                ->get();

            if ($transactions->isNotEmpty()) {
                $selectedTransactions = $transactions->random(min(5, $transactions->count()));
                
                foreach ($selectedTransactions as $transaction) {
                    BudgetDetail::create([
                        'budget_id' => $budget->id,
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->amount,
                    ]);
                }
            }
        }
    }
}
