<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->branch = Branch::factory()->create();
        $this->category = Category::factory()->create();
        $this->posCategory = Category::factory()->create(['id' => 3]);
    }

    /** @test */
    public function it_can_get_all_transactions()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Transaction::factory()->count(3)->create();

        $response = $this->getJson('/api/transaction');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_get_transaction_details_with_meta()
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        // Create transactions with different dates
        Transaction::factory()->create([
            'transaction_date' => Carbon::now('Asia/Jakarta')->toDateString(),
            'is_locked' => false
        ]);
        
        Transaction::factory()->create([
            'transaction_date' => Carbon::now('Asia/Jakarta')->subDay()->toDateString(),
            'is_locked' => true
        ]);

        $response = $this->getJson('/api/transaction-detail');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'transaction_date',
                            'description',
                            'is_locked',
                            'user' => ['id', 'name'],
                            'branch' => ['id', 'name'],
                            'category' => ['id', 'name', 'type']
                        ]
                    ],
                    'meta' => [
                        'total',
                        'today_count',
                        'locked_count',
                        'status'
                    ]
                ])
                ->assertJson([
                    'meta' => [
                        'total' => 2,
                        'today_count' => 1,
                        'locked_count' => 1,
                        'status' => 'success'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_transactions_by_branch()
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();

        Transaction::factory()->count(2)->create(['branch_id' => $branch1->id]);
        Transaction::factory()->create(['branch_id' => $branch2->id]);

        $response = $this->getJson("/api/transaction-branch/{$branch1->id}");

        $response->assertStatus(200)
                ->assertJsonPath('meta.total', 2);
    }

    /** @test */
    public function it_can_create_a_new_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $transactionData = [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'amount' => 150.50,
            'description' => 'Test transaction',
            'transaction_date' => '2024-12-01'
        ];

        $response = $this->postJson('/api/transaction', $transactionData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'amount',
                        'transaction_date',
                        'description',
                        'is_locked',
                        'user',
                        'branch',
                        'category'
                    ]
                ])
                ->assertJsonPath('data.amount', 150.50)
                ->assertJsonPath('data.description', 'Test transaction');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'amount' => 150.50,
            'description' => 'Test transaction'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_transaction()
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $response = $this->postJson('/api/transaction', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'user_id',
                    'branch_id',
                    'category_id',
                    'amount',
                    'transaction_date'
                ]);
    }

    /** @test */
    public function it_validates_amount_is_numeric_and_positive()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $transactionData = [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'amount' => -50,
            'transaction_date' => '2024-12-01'
        ];

        $response = $this->postJson('/api/transaction', $transactionData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_can_show_a_specific_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id
        ]);

        $response = $this->getJson("/api/transaction/{$transaction->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.id', $transaction->id)
                ->assertJsonPath('data.user.id', $user->id)
                ->assertJsonPath('data.branch.id', $branch->id)
                ->assertJsonPath('data.category.id', $category->id);
    }

    /** @test */
    public function it_returns_404_when_transaction_not_found()
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $response = $this->getJson('/api/transaction/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Transaction not found']);
    }

    /** @test */
    public function it_can_update_a_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id
        ]);

        $newCategory = Category::factory()->create();
        $updateData = [
            'category_id' => $newCategory->id,
            'amount' => 200.75,
            'description' => 'Updated transaction',
            'transaction_date' => '2024-12-02'
        ];

        $response = $this->patchJson("/api/transaction/{$transaction->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('data.amount', 200.75)
                ->assertJsonPath('data.description', 'Updated transaction')
                ->assertJsonPath('data.category.id', $newCategory->id);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $newCategory->id,
            'amount' => 200.75,
            'description' => 'Updated transaction'
        ]);
    }

    /** @test */
    public function it_can_delete_a_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $transaction = Transaction::factory()->create();

        $response = $this->deleteJson("/api/transaction/{$transaction->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id
        ]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_transaction()
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $response = $this->deleteJson('/api/transaction/999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Transaction not found']);
    }

    /** @test */
    public function it_can_lock_an_unlocked_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $transaction = Transaction::factory()->create(['is_locked' => false]);

        $response = $this->patchJson("/api/transaction-lock/{$transaction->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Transaksi berhasil dikunci',
                    'data' => [
                        'id' => $transaction->id,
                        'is_locked' => true
                    ]
                ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_locked' => true
        ]);
    }

    /** @test */
    public function it_can_unlock_a_locked_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $transaction = Transaction::factory()->create(['is_locked' => true]);

        $response = $this->patchJson("/api/transaction-lock/{$transaction->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Transaksi berhasil dibuka',
                    'data' => [
                        'id' => $transaction->id,
                        'is_locked' => false
                    ]
                ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_locked' => false
        ]);
    }

    /** @test */
    public function it_throws_exception_when_locking_non_existent_transaction()
    {
        $response = $this->postJson('/api/transactions/999/lock');

        $response->assertStatus(404);
    }

    /** @test */
    // public function it_can_get_pos_transactions()
    // {
    //     $user = User::factory()->create();
    //     $this->actingAs($user, 'sanctum');

    //     $branch = Branch::factory()->create();

    //     // Kategori reguler (misalnya pengeluaran)
    //     $regularCategory = Category::factory()->create([
    //         'category_type' => 'pengeluaran'
    //     ]);

    //     // Buat transaksi reguler
    //     Transaction::factory()->count(2)->create([
    //         'user_id' => $user->id,
    //         'branch_id' => $branch->id,
    //         'category_id' => $regularCategory->id,
    //         'amount' => 100
    //     ]);

    //     // Kategori POS (pemasukan)
    //     $posCategory = Category::factory()->create([
    //         'category_type' => 'pemasukan'
    //     ]);

    //     // Buat transaksi POS
    //     Transaction::factory()->count(3)->create([
    //         'user_id' => $user->id,
    //         'branch_id' => $branch->id,
    //         'category_id' => $posCategory->id,
    //         'amount' => 250
    //     ]);

    //     $response = $this->getJson('/api/transaction-pos');

    //     $response->assertStatus(200)
    //             ->assertJsonPath('meta.total', 3)
    //             ->assertJsonPath('meta.total_amount', 750)
    //             ->assertJsonCount(3, 'data');

    //     // Pastikan semua transaksi yang dikembalikan punya category_type 'pemasukan'
    //     $transactions = $response->json('data');
    //     foreach ($transactions as $transaction) {
    //         $this->assertEquals('pemasukan', $transaction['category']['type']);
    //     }
    // }

    /** @test */
    public function it_orders_transactions_by_date_descending()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $oldTransaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'transaction_date' => '2024-01-01'
        ]);

        $newTransaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'transaction_date' => '2024-12-01'
        ]);

        $response = $this->getJson('/api/transaction-detail');

        $response->assertStatus(200);

        $transactions = $response->json('data');

        $this->assertEquals($newTransaction->id, $transactions[0]['id']);
        $this->assertEquals($oldTransaction->id, $transactions[1]['id']);
    }

    /** @test */
    public function it_includes_related_models_in_response()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $branch = Branch::factory()->create();
        $category = Category::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id
        ]);

        $response = $this->getJson("/api/transaction/{$transaction->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user' => ['id', 'name'],
                        'branch' => ['id', 'name'],
                        'category' => ['id', 'name', 'type']
                    ]
                ]);
    }
}
