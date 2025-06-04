<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_can_be_created_with_factory()
    {
        $transaction = Transaction::factory()->create();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
        ]);
    }

    public function test_transaction_has_correct_fillable_attributes()
    {
        $transaction = new Transaction();

        $this->assertEquals([
            'user_id',
            'branch_id',
            'category_id',
            'amount',
            'description',
            'transaction_date',
            'is_locked',
        ], $transaction->getFillable());
    }

    public function test_transaction_has_proper_cast_for_transaction_date()
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->transaction_date);
    }

    public function test_transaction_belongs_to_user_branch_and_category()
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertInstanceOf(Branch::class, $transaction->branch);
        $this->assertInstanceOf(Category::class, $transaction->category);
    }
}
