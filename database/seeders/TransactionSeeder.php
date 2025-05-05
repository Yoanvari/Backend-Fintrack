<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Faker\Factory as Faker;


class TransactionSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $users = User::all();
        $incomeCategories = Category::where('category_type', 'income')->get();
        $expenseCategories = Category::where('category_type', 'expense')->get();

        // Income Transactions
        for ($i = 0; $i < 50; $i++) {
            Transaction::create([
                'user_id' => $users->random()->id,
                'branch_id' => $users->random()->branch_id,
                'category_id' => $incomeCategories->random()->id,
                'amount' => $this->getIncomeAmount($incomeCategories->random()->category_name, $faker),
                'description' => $this->getIncomeDescription($incomeCategories->random()->category_name, $faker),
                'transaction_date' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // Expense Transactions
        for ($i = 0; $i < 30; $i++) {
            Transaction::create([
                'user_id' => $users->random()->id,
                'branch_id' => $users->random()->branch_id,
                'category_id' => $expenseCategories->random()->id,
                'amount' => $this->getExpenseAmount($expenseCategories->random()->category_name, $faker),
                'description' => $this->getExpenseDescription($expenseCategories->random()->category_name, $faker),
                'transaction_date' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }

    private function getIncomeAmount($categoryName, $faker)
    {
        switch ($categoryName) {
            case 'Membership': 
                return $faker->numberBetween(500000, 2000000);
            case 'Kelas Fitness': 
                return $faker->numberBetween(100000, 500000);
            case 'Sewa Lapangan': 
                return $faker->numberBetween(150000, 1000000);
            default: 
                return $faker->numberBetween(50000, 500000);
        }
    }

    private function getExpenseAmount($categoryName, $faker)
    {
        switch ($categoryName) {
            case 'Gaji Staff': 
                return $faker->numberBetween(3000000, 10000000);
            case 'Gaji Instruktur': 
                return $faker->numberBetween(5000000, 15000000);
            case 'Pemeliharaan Peralatan': 
                return $faker->numberBetween(500000, 5000000);
            default: 
                return $faker->numberBetween(100000, 3000000);
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
