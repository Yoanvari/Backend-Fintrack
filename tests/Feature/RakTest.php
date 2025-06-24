<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

class RakTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $branch;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->branch = Branch::factory()->create();
        $this->category = Category::factory()->create();
        
        // Authenticate user with Sanctum
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_rak_detail_by_branch()
    {
        $budget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'status' => 'disetujui'
        ]);

        BudgetDetail::factory()->count(3)->create([
            'budget_id' => $budget->id,
            'category_id' => $this->category->id,
            'amount' => 1000
        ]);

        $response = $this->getJson("/api/rak-branch/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'period',
                             'submission_date',
                             'status',
                             'revision_note',
                             'total_amount',
                             'user' => ['id', 'name'],
                             'branch' => ['id', 'name'],
                             'detail' => [
                                 '*' => [
                                     'id',
                                     'description',
                                     'amount'
                                 ]
                             ],
                             'updated_at'
                         ]
                     ]
                 ])
                 ->assertJsonPath('data.0.id', $budget->id)
                 ->assertJsonPath('data.0.total_amount', '3000.00')
                 ->assertJsonPath('data.0.branch.id', $this->branch->id)
                 ->assertJsonCount(3, 'data.0.detail');
    }

    /** @test */
    public function it_orders_rak_by_updated_at_desc()
    {
        $oldBudget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'updated_at' => now()->subDays(2)
        ]);

        $newBudget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/rak-branch/{$this->branch->id}");

        $response->assertStatus(200);
        
        $budgets = $response->json('data');
        $this->assertEquals($newBudget->id, $budgets[0]['id']);
        $this->assertEquals($oldBudget->id, $budgets[1]['id']);
    }

    /** @test */
    public function it_can_get_rak_by_id()
    {
        $budget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id
        ]);

        BudgetDetail::factory()->count(2)->create([
            'budget_id' => $budget->id,
            'amount' => 2500
        ]);

        $response = $this->getJson("/api/rak/{$budget->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'period',
                    'submission_date',
                    'status',
                    'revision_note',
                    'total_amount',
                    'user' => ['id', 'name'],
                    'branch' => ['id', 'name'],
                    'detail' => [
                        '*' => [
                            'id',
                            'description',
                            'amount'
                        ]
                    ],
                    'updated_at'
                ]
            ])
            ->assertJsonPath('data.id', $budget->id)
            ->assertJsonPath('data.total_amount', '5000.00')
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.branch.id', $this->branch->id);
    }

    /** @test */
    public function it_returns_404_when_rak_not_found()
    {
        $response = $this->getJson('/api/rak/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Budget not found']);
    }

    /** @test */
    public function it_can_get_rak_summary_by_branch()
    {
        $budget1 = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id
        ]);

        $budget2 = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $budget1->id,
            'amount' => 1500
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $budget2->id,
            'amount' => 2500
        ]);

        $response = $this->getJson("/api/rak-summary/{$this->branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'period',
                             'submission_date',
                             'status',
                             'revision_note',
                             'total_amount',
                             'user' => ['id', 'name'],
                             'branch' => ['id', 'name'],
                             'updated_at'
                         ]
                     ]
                 ])
                 ->assertJsonCount(2, 'data');

        // Verify that detail is not included in summary
        $this->assertArrayNotHasKey('detail', $response->json('data.0'));
    }

    /** @test */
    public function it_can_get_all_rak_excluding_draft()
    {
        // Create draft budget (should be excluded)
        Budget::factory()->create([
            'status' => 'draf',
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id
        ]);

        // Create non-draft budgets (should be included)
        $approvedBudget = Budget::factory()->create([
            'status' => 'disetujui',
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id
        ]);

        $submittedBudget = Budget::factory()->create([
            'status' => 'diajukan',
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $approvedBudget->id,
            'amount' => 1000
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $submittedBudget->id,
            'amount' => 2000
        ]);

        $response = $this->getJson('/api/rak');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        $budgetIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($approvedBudget->id, $budgetIds);
        $this->assertContains($submittedBudget->id, $budgetIds);
    }

    /** @test */
    public function it_can_update_rak_status()
    {
        $budget = Budget::factory()->create([
            'status' => 'diajukan',
            'revision_note' => null
        ]);

        $updateData = [
            'status' => 'disetujui',
            'revision_note' => 'Approved with conditions'
        ];

        $response = $this->patchJson("/api/rak-status/{$budget->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Status berhasil diupdate',
                     'data' => [
                         'id' => $budget->id,
                         'status' => 'disetujui',
                         'revision_note' => 'Approved with conditions'
                     ]
                 ]);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'status' => 'disetujui',
            'revision_note' => 'Approved with conditions'
        ]);
    }

    /** @test */
    public function it_validates_status_update_request()
    {
        $budget = Budget::factory()->create();

        $response = $this->patchJson("/api/rak-status/{$budget->id}", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_status_max_length()
    {
        $budget = Budget::factory()->create();

        $response = $this->patchJson("/api/rak-status/{$budget->id}", [
            'status' => str_repeat('a', 25), // 25 characters, exceeds max 24
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_revision_note_max_length()
    {
        $budget = Budget::factory()->create();

        $response = $this->patchJson("/api/rak-status/{$budget->id}", [
            'status' => 'revisi',
            'revision_note' => str_repeat('a', 256), // 256 characters, exceeds max 255
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['revision_note']);
    }

    /** @test */
    public function it_throws_exception_when_updating_non_existent_budget_status()
    {
        $response = $this->patchJson('/api/rak-status/999', [
            'status' => 'disetujui'
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_delete_all_budget_details_by_budget_id()
    {
        $budget = Budget::factory()->create();
        
        BudgetDetail::factory()->count(5)->create([
            'budget_id' => $budget->id
        ]);

        // Create details for another budget (should not be deleted)
        $anotherBudget = Budget::factory()->create();
        BudgetDetail::factory()->count(2)->create([
            'budget_id' => $anotherBudget->id
        ]);

        $response = $this->deleteJson("/api/rak/{$budget->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Semua rincian anggaran berhasil dihapus.',
                     'deleted_count' => 5
                 ]);

        // Verify that only the targeted budget details were deleted
        $this->assertDatabaseMissing('budget_details', [
            'budget_id' => $budget->id
        ]);

        $this->assertDatabaseHas('budget_details', [
            'budget_id' => $anotherBudget->id
        ]);
    }

    /** @test */
    public function it_returns_zero_when_deleting_budget_details_with_no_details()
    {
        $budget = Budget::factory()->create();

        $response = $this->deleteJson("/api/rak/{$budget->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Semua rincian anggaran berhasil dihapus.',
                     'deleted_count' => 0
                 ]);
    }

    /** @test */
    public function it_calculates_total_amount_correctly()
    {
        $budget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $budget->id,
            'amount' => 1500.50
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $budget->id,
            'amount' => 2250.75
        ]);

        BudgetDetail::factory()->create([
            'budget_id' => $budget->id,
            'amount' => 1000.25
        ]);

        $response = $this->getJson("/api/rak/{$budget->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.total_amount', '4751.50');
    }

    /** @test */
    public function it_includes_related_models_in_response()
    {
        $budget = Budget::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/rak/{$budget->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'user' => ['id', 'name'],
                         'branch' => ['id', 'name']
                     ]
                 ])
                 ->assertJsonPath('data.user.id', $this->user->id)
                 ->assertJsonPath('data.branch.id', $this->branch->id);
    }

    /** @test */
    public function it_filters_different_statuses_correctly()
    {
        $statuses = ['draf', 'diajukan', 'disetujui', 'ditolak', 'revisi'];

        foreach ($statuses as $status) {
            Budget::factory()->create([
                'status' => $status,
                'branch_id' => $this->branch->id,
                'user_id' => $this->user->id
            ]);
        }

        $response = $this->getJson('/api/rak');

        $response->assertStatus(200)
                 ->assertJsonCount(4, 'data'); // Should exclude 'draf' status

        $returnedStatuses = collect($response->json('data'))->pluck('status')->toArray();
        $this->assertNotContains('draf', $returnedStatuses);
        $this->assertContains('diajukan', $returnedStatuses);
        $this->assertContains('disetujui', $returnedStatuses);
        $this->assertContains('ditolak', $returnedStatuses);
        $this->assertContains('revisi', $returnedStatuses);
    }
}
