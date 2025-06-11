<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LockPreviousMonthTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lock:transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $lastMonth = now()->subMonth();
        
        // \DB::table('transactions')
        //     ->whereMonth('transaction_date', $lastMonth->month)
        //     ->whereYear('transaction_date', $lastMonth->year)
        //     ->update(['is_locked' => 1]);

        \DB::table('transactions')
            ->where('transaction_date', '<', now()->startOfMonth())
            ->update(['is_locked' => 1]);

        $this->info('Transaksi bulan sebelumnya berhasil dikunci.');
    }
}
