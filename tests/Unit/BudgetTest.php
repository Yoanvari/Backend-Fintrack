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

//Runtime:       PHP 8.4.7
//Configuration: D:\Fintrack\Backend-Fintrack\phpunit.xml

//....                                                                4 / 4 (100%)

//Time: 00:00.588, Memory: 40.00 MB

//OK, but there were issues!
//Tests: 4, Assertions: 4, PHPUnit Deprecations: 9.

// 1.  atribut yang bisa diisi massal (mass assignable) di model Budget sudah benar dan lengkap sesuai daftar: ['master_budget_id', 'user_id', 'category_id', 'name', 'amount', 'description'].
// 2. hubungan Budget "belongs to" User berhasil dipanggil dan terhubung.
// 3. Hubungan budget->category() mengembalikan model Category
// 4. budget->masterBudget() mengembalikan model MasterBudget sesuai ekspektasi.
