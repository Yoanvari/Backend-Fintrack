<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Budget;
use App\Models\User;
use App\Models\Branch;
use App\Models\BudgetDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_budget()
    {
        $budget = Budget::factory()->create();

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'user_id' => $budget->user_id,
            'branch_id' => $budget->branch_id,
        ]);
    }

    /** @test */
    public function it_has_relationship_with_user()
    {
        $budget = Budget::factory()->create();

        $this->assertInstanceOf(User::class, $budget->user);
    }

    /** @test */
    public function it_has_relationship_with_branch()
    {
        $budget = Budget::factory()->create();

        $this->assertInstanceOf(Branch::class, $budget->branch);
    }

    /** @test */
    public function it_has_many_details()
    {
        $budget = Budget::factory()->create();
        $details = BudgetDetail::factory()->count(3)->create([
            'budget_id' => $budget->id
        ]);

        $this->assertCount(3, $budget->detail);
        $this->assertInstanceOf(BudgetDetail::class, $budget->detail->first());
    }

    /** @test */
    public function deleting_budget_also_deletes_its_details()
    {
        $budget = Budget::factory()->create();
        BudgetDetail::factory()->count(2)->create([
            'budget_id' => $budget->id
        ]);

        $this->assertDatabaseCount('budget_details', 2);

        $budget->delete();

        $this->assertDatabaseCount('budget_details', 0);
    }
}
