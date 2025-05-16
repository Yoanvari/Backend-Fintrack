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
            'category_type' => 'expense',
        ];

         $category = Category::create($data);

        $this->assertDatabaseHas('categories', $data);
        $this->assertEquals('Test Category', $category->category_name);
        $this->assertEquals('expense', $category->category_type);
    }

    /** @test */
    public function it_requires_fillable_attributes()
    {
        $category = new Category();

        $category->category_name = 'Food';
        $category->category_type = 'income';

        $this->assertEquals('Food', $category->category_name);
        $this->assertEquals('income', $category->category_type);
    }
}

//Runtime:       PHP 8.4.7
//Configuration: D:\Fintrack\Backend-Fintrack\phpunit.xml

//..                                                                  2 / 2 (100%)

//Time: 00:00.405, Memory: 38.00 MB

//OK, but there were issues!
//Tests: 2, Assertions: 5, PHPUnit Deprecations: 9.
// test lolos dan berhasil