<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Branch;
use App\Models\Category;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $branches = Branch::all();

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
            $userIdRange = match ($branch->id) {
                1 => range(2, 6),
                2 => range(7, 11),
                3 => range(12, 16),
                4 => range(17, 21),
                default => [],
            };

            if (empty($userIdRange)) continue;

            $userId = $faker->randomElement($userIdRange);
            $user = User::find($userId);

            if (!$user) continue;

            // 5 budgets disetujui dari 6 bulan lalu sampai 1 bulan lalu
            for ($i = 0; $i < 5; $i++) {
                $period = Carbon::now()->subMonths(rand(6, 1))->startOfMonth();
                $this->createBudget($faker, $branch, $user, 'disetujui', $period, $categories, $descriptionsByCategory);
            }

            // 2 budgets ditolak dari 6 bulan lalu sampai sekarang
            for ($i = 0; $i < 2; $i++) {
                $period = $faker->dateTimeBetween('-6 months', 'now');
                $this->createBudget($faker, $branch, $user, 'ditolak', Carbon::parse($period), $categories, $descriptionsByCategory, $revisiNoteByCategory);
            }

            // 1 budget revisi untuk bulan ini
            $period = Carbon::now()->startOfMonth();
            $this->createBudget($faker, $branch, $user, 'revisi', $period, $categories, $descriptionsByCategory, $revisiNoteByCategory);

            // 1 budget draf untuk bulan depan
            $period = Carbon::now()->addMonth()->startOfMonth();
            $this->createBudget($faker, $branch, $user, 'draf', $period, $categories, $descriptionsByCategory);
        }
    }

    private function createBudget($faker, $branch, $user, $status, $period, $categories, $descriptionsByCategory, $revisiNoteByCategory = [])
    {
        $userIdRange = match ($branch->id) {
            1 => range(2, 6),
            2 => range(7, 11),
            3 => range(12, 16),
            4 => range(17, 21),
            default => [],
        };
    
        if (empty($userIdRange)) return;
    
        $userId = $faker->randomElement($userIdRange);
        $user = User::find($userId);
    
        if (!$user) return;

        $budget = Budget::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'period' => $period->format('Y-m-d'),
            'submission_date' => $period->lessThanOrEqualTo(now())
                ? $faker->dateTimeBetween($period->copy()->subWeeks(2), now())
                : now(),
            'status' => $status,
        ]);

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

        if ($status === 'revisi') {
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

