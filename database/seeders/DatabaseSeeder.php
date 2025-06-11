<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            TransactionSeeder::class,
            PosTransactionSeeder::class,
            BudgetSeeder::class,
            // BudgetDetailSeeder::class,
        ]);
    }
}
