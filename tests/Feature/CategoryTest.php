<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup tambahan jika diperlukan
    }

    /**
     * Test untuk mendapatkan semua kategori (GET /categories)
     */
    public function test_can_get_all_categories()
    {
        // Arrange - Buat beberapa kategori untuk testing
        Category::factory()->count(5)->create();

        // Act - Panggil endpoint
        $response = $this->getJson('/api/categories');

        // Assert - Verifikasi response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'category_name',
                        'category_type',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertCount(5, $response->json());
    }

    /**
     * Test untuk mendapatkan semua kategori ketika data kosong
     */
    public function test_can_get_empty_categories_list()
    {
        // Act
        $response = $this->getJson('/api/categories');

        // Assert
        $response->assertStatus(200)
                ->assertJson([]);
    }

    /**
     * Test untuk membuat kategori baru dengan tipe pemasukan (POST /categories)
     */
    public function test_can_create_new_category_pemasukan()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Gaji',
            'category_type' => 'pemasukan'
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'category_name',
                    'category_type',
                    'created_at',
                    'updated_at'
                ])
                ->assertJson([
                    'category_name' => 'Gaji',
                    'category_type' => 'pemasukan'
                ]);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('categories', $categoryData);
    }

    /**
     * Test untuk membuat kategori baru dengan tipe pengeluaran
     */
    public function test_can_create_new_category_pengeluaran()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Makanan',
            'category_type' => 'pengeluaran'
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(201)
                ->assertJson([
                    'category_name' => 'Makanan',
                    'category_type' => 'pengeluaran'
                ]);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('categories', $categoryData);
    }

    /**
     * Test validasi saat membuat kategori dengan data kosong
     */
    public function test_create_category_validation_fails_with_empty_data()
    {
        // Act
        $response = $this->postJson('/api/categories', []);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'category_name',
                    'category_type'
                ]);
    }

    /**
     * Test validasi saat membuat kategori dengan category_name terlalu panjang
     */
    public function test_create_category_validation_fails_with_long_category_name()
    {
        // Arrange
        $categoryData = [
            'category_name' => str_repeat('A', 33), // Lebih dari 32 karakter
            'category_type' => 'pemasukan'
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_name']);
    }

    /**
     * Test validasi saat membuat kategori dengan category_type terlalu panjang
     */
    public function test_create_category_validation_fails_with_long_category_type()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Transport',
            'category_type' => str_repeat('B', 17) // Lebih dari 16 karakter
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_type']);
    }

    /**
     * Test validasi dengan missing category_name
     */
    public function test_create_category_validation_fails_without_category_name()
    {
        // Arrange
        $categoryData = [
            'category_type' => 'pemasukan'
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_name']);
    }

    /**
     * Test validasi dengan missing category_type
     */
    public function test_create_category_validation_fails_without_category_type()
    {
        // Arrange
        $categoryData = [
            'category_name' => 'Bonus'
        ];

        // Act
        $response = $this->postJson('/api/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_type']);
    }

    /**
     * Test untuk mendapatkan kategori berdasarkan ID (GET /categories/{id})
     */
    public function test_can_get_category_by_id()
    {
        // Arrange
        $category = Category::factory()->create([
            'category_name' => 'Investasi',
            'category_type' => 'pemasukan'
        ]);

        // Act
        $response = $this->getJson("/api/categories/{$category->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'id' => $category->id,
                    'category_name' => 'Investasi',
                    'category_type' => 'pemasukan'
                ]);
    }

    /**
     * Test untuk mendapatkan kategori yang tidak ada
     */
    public function test_get_category_returns_404_when_not_found()
    {
        // Act
        $response = $this->getJson('/api/categories/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Category not found'
                ]);
    }

    /**
     * Test untuk update kategori (PUT/PATCH /categories/{id})
     */
    public function test_can_update_category()
    {
        // Arrange
        $category = Category::factory()->create([
            'category_name' => 'Transport',
            'category_type' => 'pengeluaran'
        ]);

        $updateData = [
            'category_name' => 'Transportasi',
            'category_type' => 'pengeluaran'
        ];

        // Act
        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'id' => $category->id,
                    'category_name' => 'Transportasi',
                    'category_type' => 'pengeluaran'
                ]);

        // Verifikasi data terupdate di database
        $this->assertDatabaseHas('categories', array_merge(['id' => $category->id], $updateData));
    }

    /**
     * Test update kategori dengan mengubah tipe
     */
    public function test_can_update_category_type()
    {
        // Arrange
        $category = Category::factory()->create([
            'category_name' => 'Freelance',
            'category_type' => 'pengeluaran'
        ]);

        $updateData = [
            'category_name' => 'Freelance',
            'category_type' => 'pemasukan' // Ubah dari pengeluaran ke pemasukan
        ];

        // Act
        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'id' => $category->id,
                    'category_name' => 'Freelance',
                    'category_type' => 'pemasukan'
                ]);

        // Verifikasi data terupdate di database
        $this->assertDatabaseHas('categories', $updateData);
    }

    /**
     * Test update kategori dengan data tidak valid
     */
    public function test_update_category_validation_fails_with_invalid_data()
    {
        // Arrange
        $category = Category::factory()->create();

        $invalidData = [
            'category_name' => '', // Empty string
            'category_type' => str_repeat('X', 20) // Terlalu panjang
        ];

        // Act
        $response = $this->putJson("/api/categories/{$category->id}", $invalidData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'category_name',
                    'category_type'
                ]);
    }

    /**
     * Test update kategori yang tidak ada
     */
    public function test_update_category_returns_404_when_not_found()
    {
        // Arrange
        $updateData = [
            'category_name' => 'Test Category',
            'category_type' => 'pemasukan'
        ];

        // Act
        $response = $this->putJson('/api/categories/999', $updateData);

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Category not found'
                ]);
    }

    /**
     * Test untuk menghapus kategori (DELETE /categories/{id})
     */
    public function test_can_delete_category()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->deleteJson("/api/categories/{$category->id}");

        // Assert
        $response->assertStatus(204);

        // Verifikasi data terhapus dari database
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test untuk menghapus kategori yang tidak ada
     */
    public function test_delete_category_returns_404_when_not_found()
    {
        // Act
        $response = $this->deleteJson('/api/categories/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Category not found'
                ]);
    }

    /**
     * Test untuk memastikan factory menghasilkan data yang valid
     */
    public function test_category_factory_creates_valid_data()
    {
        // Act
        $category = Category::factory()->create();

        // Assert
        $this->assertNotNull($category->category_name);
        $this->assertNotNull($category->category_type);
        $this->assertContains($category->category_type, ['pemasukan', 'pengeluaran']);
        $this->assertLessThanOrEqual(32, strlen($category->category_name));
        $this->assertLessThanOrEqual(16, strlen($category->category_type));
    }

    /**
     * Test untuk memastikan response format konsisten
     */
    public function test_response_format_consistency()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act & Assert untuk GET single
        $response = $this->getJson("/api/categories/{$category->id}");
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'category_name',
                    'category_type',
                    'created_at',
                    'updated_at'
                ]);

        // Act & Assert untuk GET all
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'category_name',
                        'category_type',
                        'created_at',
                        'updated_at'
                    ]
                ]);
    }

    /**
     * Test untuk memastikan kategori dapat dibuat dengan berbagai tipe
     */
    public function test_can_create_multiple_category_types()
    {
        // Arrange & Act
        $pemasukan = Category::factory()->create(['category_type' => 'pemasukan']);
        $pengeluaran = Category::factory()->create(['category_type' => 'pengeluaran']);

        // Assert
        $this->assertEquals('pemasukan', $pemasukan->category_type);
        $this->assertEquals('pengeluaran', $pengeluaran->category_type);
        $this->assertDatabaseHas('categories', ['category_type' => 'pemasukan']);
        $this->assertDatabaseHas('categories', ['category_type' => 'pengeluaran']);
    }

    /**
     * Test untuk memastikan data yang dibuat via factory sesuai dengan validasi controller
     */
    public function test_factory_data_passes_controller_validation()
    {
        // Arrange
        $factoryData = Category::factory()->raw();

        // Act
        $response = $this->postJson('/api/categories', $factoryData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', $factoryData);
    }
}
