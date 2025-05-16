<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PosTransaction;
use App\Models\Branch;
use Faker\Factory as Faker;

class PosTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create('id_ID');
        $branches = Branch::all();
        $services = $this->getPosServices();

        foreach ($branches as $branch) {
            for ($i = 0; $i < 20; $i++) {
                $service = $faker->randomElement($services);
                PosTransaction::create([
                    'reservation_id' => 'POS-' . $branch->branch_code . '-' . $faker->unique()->numberBetween(1000, 9999),
                    'branch_id' => $branch->id,
                    'total_amount' => $service['amount'],
                    'payment_status' => $faker->boolean(85),
                ]);
            }
        }
    }

    private function getPosServices()
    {
        return [
            ['name' => 'Membership 1 Bulan', 'amount' => 800000],
            ['name' => 'Membership 3 Bulan', 'amount' => 2000000],
            ['name' => 'Kelas Yoga', 'amount' => 150000],
            ['name' => 'Kelas Zumba', 'amount' => 120000],
            ['name' => 'Sewa Lapangan Badminton', 'amount' => 200000],
            ['name' => 'Sewa Lapangan Futsal', 'amount' => 500000],
            ['name' => 'Penjualan Merchandise', 'amount' => 250000]
        ];
    }
}
