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
            ['category_name' => 'Gaji Staff', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Gaji Instruktur', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Pemeliharaan Peralatan', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Listrik dan Air', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Promosi', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Alat Tulis Kantor', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Konsumsi Acara', 'category_type' => 'pengeluaran'],
            ['category_name' => 'Transportasi Staff', 'category_type' => 'pengeluaran']
        ];
    
        Category::insert($categories);
    }
}
