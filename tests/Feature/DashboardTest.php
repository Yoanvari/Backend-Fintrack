<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $branch;
    protected $incomeCategory;
    protected $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->branch = Branch::factory()->create();
        $this->incomeCategory = Category::factory()->create(['category_type' => 'pemasukan']);
        $this->expenseCategory = Category::factory()->create(['category_type' => 'pengeluaran']);
        
        // Authenticate user with Sanctum
        Sanctum::actingAs($this->user);
    }

    // ==================== Dashboard Admin Tests ====================

    /** @test */
    public function it_can_get_dashboard_summary_for_branch()
    {
        $currentYear = Carbon::now()->year;

        // Create income transactions
        Transaction::factory()->count(3)->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'created_at' => Carbon::create($currentYear, 6, 15),
            'transaction_date' => Carbon::create($currentYear, 6, 15)
        ]);

        // Create expense transactions
        Transaction::factory()->count(2)->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 500,
            'created_at' => Carbon::create($currentYear, 6, 15),
            'transaction_date' => Carbon::create($currentYear, 6, 15)
        ]);

        // Create transactions from previous year (should not be included)
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000,
            'created_at' => Carbon::create($currentYear - 1, 6, 15),
            'transaction_date' => Carbon::create($currentYear - 1, 6, 15)
        ]);

        $response = $this->getJson("/api/dashboard-summary/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'pemasukan',
                     'pengeluaran',
                     'saldo'
                 ])
                 ->assertJson([
                     'pemasukan' => 3000, // 3 × 1000
                     'pengeluaran' => 1000, // 2 × 500
                     'saldo' => 2000 // 3000 - 1000
                 ]);
    }

    /** @test */
    public function it_can_get_trend_chart_data_for_branch()
    {
        // Create transactions in different months
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1500,
            'transaction_date' => Carbon::create(2024, 1, 15)
        ]);

        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 800,
            'transaction_date' => Carbon::create(2024, 1, 20)
        ]);

        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000,
            'transaction_date' => Carbon::create(2024, 2, 10)
        ]);

        $response = $this->getJson("/api/dashboard-trendchart/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'labels',
                     'datasets' => [
                         '*' => [
                             'label',
                             'data',
                             'backgroundColor'
                         ]
                     ]
                 ]);

        $data = $response->json();
        $this->assertIsArray($data['labels']);
        $this->assertCount(2, $data['datasets']); // Pemasukan and Pengeluaran
        $this->assertEquals('Pengeluaran', $data['datasets'][0]['label']);
        $this->assertEquals('Pemasukan', $data['datasets'][1]['label']);
    }

    /** @test */
    public function it_can_get_yearly_trend_chart_data_for_branch()
    {
        // Create transactions in different years
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000,
            'transaction_date' => Carbon::create(2023, 6, 15)
        ]);

        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 2000,
            'transaction_date' => Carbon::create(2023, 8, 20)
        ]);

        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 8000,
            'transaction_date' => Carbon::create(2024, 3, 10)
        ]);

        $response = $this->getJson("/api/dashboard-trendchart-yearly/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'year',
                         'total'
                     ]
                 ]);

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        
        foreach ($data as $item) {
            $this->assertArrayHasKey('year', $item);
            $this->assertArrayHasKey('total', $item);
        }
    }

    /** @test */
    public function it_can_get_recent_transactions_for_branch()
    {
        // Create 15 transactions (should only return 10 most recent)
        for ($i = 1; $i <= 15; $i++) {
            Transaction::factory()->create([
                'branch_id' => $this->branch->id,
                'category_id' => $this->incomeCategory->id,
                'amount' => 100 * $i,
                'transaction_date' => Carbon::now()->subDays($i)
            ]);
        }

        $response = $this->getJson("/api/dashboard-transactions/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'tipe',
                         'jumlah',
                         'tanggal'
                     ]
                 ]);

        $transactions = $response->json();
        $this->assertCount(10, $transactions); // Should return only 10 transactions
        
        // Verify the structure and data types
        foreach ($transactions as $transaction) {
            $this->assertArrayHasKey('tipe', $transaction);
            $this->assertArrayHasKey('jumlah', $transaction);
            $this->assertArrayHasKey('tanggal', $transaction);
            $this->assertEquals('pemasukan', $transaction['tipe']);
        }

        // Verify ordering (most recent first)
        $this->assertEquals(100, $transactions[0]['jumlah']); // Most recent (i=1)
        $this->assertEquals(1000, $transactions[9]['jumlah']); // 10th most recent (i=10)
    }

    // ==================== Dashboard SuperAdmin Tests ====================

    /** @test */
    public function it_can_get_superadmin_dashboard_summary()
    {
        // Create multiple branches with transactions
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();

        // Branch 1 transactions
        Transaction::factory()->create([
            'branch_id' => $branch1->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000
        ]);

        Transaction::factory()->create([
            'branch_id' => $branch1->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 2000
        ]);

        // Branch 2 transactions
        Transaction::factory()->create([
            'branch_id' => $branch2->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 3000
        ]);

        Transaction::factory()->create([
            'branch_id' => $branch2->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 1500
        ]);

        $response = $this->getJson('/api/superadmin/dashboard-summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'summary' => [
                         'total_pemasukan',
                         'total_pengeluaran',
                         'total_saldo',
                         'jumlah_cabang'
                     ],
                     'branches' => [
                         '*' => [
                             'branch_code',
                             'branch_name',
                             'branch_address',
                             'pemasukan',
                             'pengeluaran',
                             'saldo'
                         ]
                     ]
                 ]);

        $data = $response->json();
        $this->assertEquals(8000, $data['summary']['total_pemasukan']); // 5000 + 3000
        $this->assertEquals(3500, $data['summary']['total_pengeluaran']); // 2000 + 1500
        $this->assertEquals(4500, $data['summary']['total_saldo']); // 8000 - 3500
        $this->assertGreaterThanOrEqual(2, $data['summary']['jumlah_cabang']);
        $this->assertIsArray($data['branches']);
    }

    /** @test */
    public function it_can_get_superadmin_trend_line_data()
    {
        // Create transactions in different years
        Transaction::factory()->create([
            'category_id' => $this->incomeCategory->id,
            'amount' => 12000,
            'transaction_date' => Carbon::create(2022, 6, 15)
        ]);

        Transaction::factory()->create([
            'category_id' => $this->expenseCategory->id,
            'amount' => 5000,
            'transaction_date' => Carbon::create(2022, 8, 20)
        ]);

        Transaction::factory()->create([
            'category_id' => $this->incomeCategory->id,
            'amount' => 15000,
            'transaction_date' => Carbon::create(2023, 3, 10)
        ]);

        Transaction::factory()->create([
            'category_id' => $this->expenseCategory->id,
            'amount' => 7000,
            'transaction_date' => Carbon::create(2023, 9, 5)
        ]);

        $response = $this->getJson('/api/superadmin/dashboard-trendline');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'labels',
                     'datasets' => [
                         '*' => [
                             'label',
                             'data',
                             'borderColor',
                             'backgroundColor'
                         ]
                     ]
                 ]);

        $data = $response->json();
        $this->assertIsArray($data['labels']);
        $this->assertCount(1, $data['datasets']);
        $this->assertEquals('Total Keuangan', $data['datasets'][0]['label']);
        $this->assertIsArray($data['datasets'][0]['data']);
    }

    /** @test */
    public function it_can_get_superadmin_trend_bar_data()
    {
        // Create transactions in different months
        $months = [
            ['Jan', 1], ['Feb', 2], ['Mar', 3], ['Apr', 4],
            ['May', 5], ['Jun', 6], ['Jul', 7], ['Aug', 8],
            ['Sep', 9], ['Oct', 10], ['Nov', 11], ['Dec', 12]
        ];

        // Create income transactions for first 6 months
        foreach (array_slice($months, 0, 6) as [$monthName, $monthNum]) {
            Transaction::factory()->create([
                'category_id' => $this->incomeCategory->id,
                'amount' => 1000 * $monthNum,
                'transaction_date' => Carbon::create(2024, $monthNum, 15)
            ]);
        }

        // Create expense transactions for first 4 months
        foreach (array_slice($months, 0, 4) as [$monthName, $monthNum]) {
            Transaction::factory()->create([
                'category_id' => $this->expenseCategory->id,
                'amount' => 500 * $monthNum,
                'transaction_date' => Carbon::create(2024, $monthNum, 20)
            ]);
        }

        $response = $this->getJson('/api/superadmin/dashboard-trendbar');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'labels',
                     'datasets' => [
                         '*' => [
                             'label',
                             'data',
                             'backgroundColor',
                             'borderColor'
                         ]
                     ]
                 ]);

        $data = $response->json();
        $expectedMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        $this->assertEquals($expectedMonths, $data['labels']);
        $this->assertCount(2, $data['datasets']);
        $this->assertEquals('Pemasukan', $data['datasets'][0]['label']);
        $this->assertEquals('Pengeluaran', $data['datasets'][1]['label']);
        $this->assertCount(12, $data['datasets'][0]['data']);
        $this->assertCount(12, $data['datasets'][1]['data']);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        // Test with no transactions
        $response = $this->getJson("/api/dashboard-summary/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'pemasukan' => 0,
                     'pengeluaran' => 0,
                     'saldo' => 0
                 ]);
    }

    /** @test */
    public function it_filters_by_branch_correctly()
    {
        $anotherBranch = Branch::factory()->create();

        // Create transactions for the target branch
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000
        ]);

        // Create transactions for another branch (should not be included)
        Transaction::factory()->create([
            'branch_id' => $anotherBranch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000
        ]);

        $response = $this->getJson("/api/dashboard-summary/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'pemasukan' => 1000, // Only from target branch
                     'pengeluaran' => 0,
                     'saldo' => 1000
                 ]);
    }

    /** @test */
    public function it_filters_by_current_year_in_admin_summary()
    {
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        // Create transaction for current year
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000,
            'created_at' => Carbon::create($currentYear, 6, 15),
            'transaction_date' => Carbon::create($currentYear, 6, 15)
        ]);

        // Create transaction for last year (should not be included)
        Transaction::factory()->create([
            'branch_id' => $this->branch->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 8000,
            'created_at' => Carbon::create($lastYear, 6, 15),
            'transaction_date' => Carbon::create($lastYear, 6, 15)
        ]);

        $response = $this->getJson("/api/dashboard-summary/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'pemasukan' => 2000, // Only current year
                     'pengeluaran' => 0,
                     'saldo' => 2000
                 ]);
    }

    /** @test */
    public function it_calculates_branch_data_correctly_in_superadmin_summary()
    {
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();

        // Branch 1: Income 6000, Expense 2000, Saldo 4000
        Transaction::factory()->count(3)->create([
            'branch_id' => $branch1->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000
        ]);

        Transaction::factory()->create([
            'branch_id' => $branch1->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 2000
        ]);

        // Branch 2: Income 4000, Expense 3000, Saldo 1000
        Transaction::factory()->count(2)->create([
            'branch_id' => $branch2->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000
        ]);

        Transaction::factory()->count(3)->create([
            'branch_id' => $branch2->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 1000
        ]);

        $response = $this->getJson('/api/superadmin/dashboard-summary');

        $response->assertStatus(200);

        $data = $response->json();
        
        // Check totals
        $this->assertEquals(10000, $data['summary']['total_pemasukan']); // 6000 + 4000
        $this->assertEquals(5000, $data['summary']['total_pengeluaran']); // 2000 + 3000
        $this->assertEquals(5000, $data['summary']['total_saldo']); // 10000 - 5000

        // Check individual branch data
        $branchData = collect($data['branches']);
        
        $branch1Data = $branchData->firstWhere('branch_name', $branch1->branch_name);
        $this->assertEquals(6000, $branch1Data['pemasukan']);
        $this->assertEquals(2000, $branch1Data['pengeluaran']);
        $this->assertEquals(4000, $branch1Data['saldo']);

        $branch2Data = $branchData->firstWhere('branch_name', $branch2->branch_name);
        $this->assertEquals(4000, $branch2Data['pemasukan']);
        $this->assertEquals(3000, $branch2Data['pengeluaran']);
        $this->assertEquals(1000, $branch2Data['saldo']);
    }
}
