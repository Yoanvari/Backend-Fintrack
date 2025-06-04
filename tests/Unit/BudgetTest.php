<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Budget;
use App\Models\User;
use App\Models\Category;
use App\Models\MasterBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_expected_fillable_fields()
    {
        $budget = new Budget();
        $this->assertEquals([
            'master_budget_id',
            'user_id',
            'category_id',
            'name',
            'amount',
            'description',
        ], $budget->getFillable());
    }

    /** @test */
    public function it_belongs_to_user()
{
    $user = User::factory()->create();

    $budget = Budget::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->assertInstanceOf(User::class, $budget->user);
}


    /** @test */
    public function it_belongs_to_category()
    {
        $budget = Budget::factory()->create();
        $this->assertInstanceOf(Category::class, $budget->category()->getModel());
    }

    /** @test */
    public function it_belongs_to_master_budget()
    {
        $budget = Budget::factory()->create();
        $this->assertInstanceOf(MasterBudget::class, $budget->masterBudget()->getModel());
    }
}
