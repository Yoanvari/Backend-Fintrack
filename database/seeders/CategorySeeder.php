<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Seed Categories (Kategori khusus sport center)
        $incomeCategories = [
            ['category_name' => 'Membership', 'category_type' => 'income'],
            ['category_name' => 'Kelas Fitness', 'category_type' => 'income'],
            ['category_name' => 'Sewa Lapangan', 'category_type' => 'income'],
            ['category_name' => 'Penjualan Merchandise', 'category_type' => 'income']
        ];

        $expenseCategories = [
            ['category_name' => 'Gaji Staff', 'category_type' => 'expense'],
            ['category_name' => 'Gaji Instruktur', 'category_type' => 'expense'],
            ['category_name' => 'Pemeliharaan Peralatan', 'category_type' => 'expense'],
            ['category_name' => 'Listrik dan Air', 'category_type' => 'expense'],
            ['category_name' => 'Promosi', 'category_type' => 'expense']
        ];

        Category::insert($incomeCategories);
        Category::insert($expenseCategories);
    }
}
