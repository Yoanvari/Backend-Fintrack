<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use App\Models\Branch;
use Faker\Factory as Faker;


class TransactionSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $users = User::all();
        $branches = Branch::all();
        $incomeCategories = Category::where('category_type', 'pemasukan')->get();
        $expenseCategories = Category::where('category_type', 'pengeluaran')->get();

        foreach ($branches as $branch) {
            $branchUsers = User::where('branch_id', $branch->id)->get();
        
            do {
                $shouldFlip = $faker->boolean(20); // 20% kemungkinan expense > income
                $incomeTransactions = [];
                $expenseTransactions = [];
                $totalIncome = 0;
                $totalExpense = 0;
        
                // Transaksi Pemasukan
                for ($i = 0; $i < 20; $i++) {
                    $category = $incomeCategories->random();
                    $TransactionUser = $branchUsers->random();
                    $amount = $this->getIncomeAmount($category->category_name, $faker);
                    $totalIncome += $amount;
        
                    $incomeTransactions[] = [
                        'user_id' => $TransactionUser->id,
                        'branch_id' => $branch->id,
                        'category_id' => $category->id,
                        'amount' => $amount,
                        'description' => $this->getIncomeDescription($category->category_name, $faker),
                        'transaction_date' => $faker->dateTimeBetween('-6 months', 'now'),
                    ];
                }
        
                // Transaksi Pengeluaran
                for ($i = 0; $i < 10; $i++) {
                    $category = $expenseCategories->random();
                    $TransactionUser = $branchUsers->random();
                    $amount = $this->getExpenseAmount($category->category_name, $faker);
                    $totalExpense += $amount;
        
                    $expenseTransactions[] = [
                        'user_id' => $TransactionUser->id,
                        'branch_id' => $branch->id,
                        'category_id' => $category->id,
                        'amount' => $amount,
                        'description' => $this->getExpenseDescription($category->category_name, $faker),
                        'transaction_date' => $faker->dateTimeBetween('-6 months', 'now'),
                    ];
                }
            } while ($totalExpense > $totalIncome && !$shouldFlip);
        
            // Simpan ke DB
            foreach ($incomeTransactions as $data) {
                Transaction::create($data);
            }
        
            foreach ($expenseTransactions as $data) {
                Transaction::create($data);
            }
        }        
    }

    private function getIncomeAmount($categoryName, $faker)
{
    switch ($categoryName) {
        case 'Membership': 
            return $faker->numberBetween(1500000, 4000000);
        case 'Kelas Fitness': 
            return $faker->numberBetween(500000, 1500000);
        case 'Sewa Lapangan': 
            return $faker->numberBetween(500000, 2000000);
        default: 
            return $faker->numberBetween(100000, 1000000);
    }
}

private function getExpenseAmount($categoryName, $faker)
{
    switch ($categoryName) {
        case 'Gaji Staff': 
            return $faker->numberBetween(2000000, 8000000);
        case 'Gaji Instruktur': 
            return $faker->numberBetween(3000000, 10000000);
        case 'Pemeliharaan Peralatan': 
            return $faker->numberBetween(300000, 2000000); 
        default: 
            return $faker->numberBetween(50000, 1500000);
    }
}

    private function getIncomeDescription($categoryName, $faker)
    {
        $descriptions = [
            'Membership' => ['Pembayaran membership oleh ', 'Perpanjangan membership '],
            'Kelas Fitness' => ['Pembayaran kelas ', 'Peserta kelas '],
            'Sewa Lapangan' => ['Sewa lapangan ', 'Penyewaan lapangan oleh ']
        ];

        $prefix = $descriptions[$categoryName] ?? ['Pendapatan dari '];
        return $faker->randomElement($prefix) . $faker->name;
    }

    private function getExpenseDescription($categoryName, $faker)
    {
        $descriptions = [
            'Gaji Staff' => ['Pembayaran gaji ', 'Transfer gaji '],
            'Pemeliharaan Peralatan' => ['Perbaikan ', 'Maintenance '],
            'Promosi' => ['Biaya iklan ', 'Sponsor event ']
        ];

        $prefix = $descriptions[$categoryName] ?? ['Pengeluaran untuk '];
        return $faker->randomElement($prefix) . $faker->word;
    }
}
