<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Branch;
use App\Models\Category;
use App\Models\User;
use Faker\Factory as Faker;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $branches = Branch::all();
        $statuses = ['draf', 'diajukan', 'disetujui', 'ditolak', 'revisi'];

        $categories = Category::where('category_type', 'pengeluaran')->get();

        $descriptionsByCategory = [
            'Gaji' => ['Gaji staff tetap', 'Gaji instruktur freelance', 'Bonus bulanan'],
            'Operasional' => ['Pembelian alat tulis', 'Biaya listrik', 'Biaya air', 'Langganan internet'],
            'Pemeliharaan' => ['Servis alat gym', 'Penggantian suku cadang', 'Perawatan bulanan'],
            'Promosi' => ['Iklan media sosial', 'Pembuatan banner', 'Flyer dan brosur'],
            'Konsumsi dan Acara' => ['Snack acara bulanan', 'Konsumsi peserta event'],
            'Transportasi' => ['Transport staff ke event', 'Biaya bensin untuk pengiriman']
        ];

        $revisiNoteByCategory = [
            'Gaji' => 'Anggaran gaji terlalu tinggi, mohon ditinjau kembali.',
            'Operasional' => 'Anggaran operasional terlalu tinggi, mohon sesuaikan kembali.',
            'Pemeliharaan' => 'Anggaran pemeliharaan melebihi batas, harap diperiksa.',
            'Promosi' => 'Anggaran promosi perlu dirasionalisasi.',
            'Konsumsi dan Acara' => 'Konsumsi acara tampaknya terlalu besar, harap dikurangi.',
            'Transportasi' => 'Transportasi memakan banyak biaya, mohon diperbaiki.',
        ];

        foreach ($branches as $branch) {
            $users = User::where('branch_id', $branch->id)->get();

            foreach ($users as $user) {
                for ($i = 0; $i < 2; $i++) {
                    $status = $faker->randomElement($statuses);

                    $budget = Budget::create([
                        'branch_id' => $branch->id,
                        'user_id' => $user->id,
                        'period' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                        'submission_date' => $faker->dateTimeBetween('-5 months', 'now'),
                        'status' => $status,
                    ]);

                    // Ambil kategori acak dan buat budget detail
                    $usedCategories = $categories->random(min(4, $categories->count()));

                    $usedCategoryNames = [];

                    foreach ($usedCategories as $category) {
                        $usedCategoryNames[] = $category->category_name;
                        $descriptionOptions = $descriptionsByCategory[$category->category_name] ?? [$faker->sentence()];
                        $description = $faker->randomElement($descriptionOptions);

                        BudgetDetail::create([
                            'budget_id' => $budget->id,
                            'category_id' => $category->id,
                            'description' => $description,
                            'amount' => $faker->randomFloat(2, 100000, 10000000),
                        ]);
                    }

                    // Set revision note jika status revisi atau ditolak
                    if ($status === 'revisi') {
                        // Pilih salah satu dari kategori yang digunakan
                        $randomCategory = $faker->randomElement($usedCategoryNames);
                        $note = $revisiNoteByCategory[$randomCategory] ?? 'Harap perbaiki anggaran yang diajukan.';
                        $budget->revision_note = $note;
                        $budget->save();
                    } elseif ($status === 'ditolak') {
                        $budget->revision_note = 'Anggaran bulan ini sudah terpenuhi sebelumnya.';
                        $budget->save();
                    }
                }
            }
        }
    }
}
