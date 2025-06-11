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

        $categories = [
            // Pemasukan
            ['category_name' => 'Membership', 'category_type' => 'pemasukan'],
            ['category_name' => 'Kelas Fitness', 'category_type' => 'pemasukan'],
            ['category_name' => 'Sewa Lapangan', 'category_type' => 'pemasukan'],
            ['category_name' => 'Penjualan Merchandise', 'category_type' => 'pemasukan'],
    
            // Pengeluaran
            ['category_name' => 'Gaji', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Operasional', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Pemeliharaan', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Promosi', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Konsumsi dan Acara', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Transportasi', 'category_type' => 'pengeluaran']
        ];
    
        Category::insert($categories);
    }
}
