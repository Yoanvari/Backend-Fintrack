<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Budget;
use App\Models\Category;
use App\Models\BudgetDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_budget_detail()
    {
        $detail = BudgetDetail::factory()->create();

        $this->assertDatabaseHas('budget_details', [
            'id' => $detail->id,
            'budget_id' => $detail->budget_id,
            'category_id' => $detail->category_id,
            'description' => $detail->description,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_budget()
    {
        $detail = BudgetDetail::factory()->create();

        $this->assertInstanceOf(Budget::class, $detail->budget);
    }

    /** @test */
    public function it_belongs_to_a_category()
    {
        $detail = BudgetDetail::factory()->create();

        $this->assertInstanceOf(Category::class, $detail->category);
    }

    /** @test */
    public function deleting_budget_detail_does_not_delete_budget()
    {
        $detail = BudgetDetail::factory()->create();
        $budgetId = $detail->budget->id;

        $detail->delete();

        $this->assertDatabaseHas('budgets', [
            'id' => $budgetId
        ]);
    }   
}
