<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_mass_assignment()
    {
        $data = [
            'category_name' => 'Test Category',
            'category_type' => 'pengeluaran',
        ];

         $category = Category::create($data);

        $this->assertDatabaseHas('categories', $data);
        $this->assertEquals('Test Category', $category->category_name);
        $this->assertEquals('pengeluaran', $category->category_type);
    }

    /** @test */
    public function it_requires_fillable_attributes()
    {
        $category = new Category();

        $category->category_name = 'Food';
        $category->category_type = 'pemasukan';

        $this->assertEquals('Food', $category->category_name);
        $this->assertEquals('pemasukan', $category->category_type);
    }
}