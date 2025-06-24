<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;

class BranchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup tambahan jika diperlukan
    }

    /**
     * Test untuk mendapatkan semua branch (GET /branches)
     */
    public function test_can_get_all_branches()
    {
        // Arrange - Buat beberapa branch untuk testing
        Branch::factory()->count(3)->create();

        // Act - Panggil endpoint
        $response = $this->getJson('/api/branches');

        // Assert - Verifikasi response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'branch_code',
                        'branch_name',
                        'branch_address',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertCount(3, $response->json());
    }

    /**
     * Test untuk mendapatkan semua branch ketika data kosong
     */
    public function test_can_get_empty_branches_list()
    {
        // Act
        $response = $this->getJson('/api/branches');

        // Assert
        $response->assertStatus(200)
                ->assertJson([]);
    }

    /**
     * Test untuk membuat branch baru (POST /branches)
     */
    public function test_can_create_new_branch()
    {
        // Arrange
        $branchData = [
            'branch_code' => 'BR001',
            'branch_name' => 'Jakarta Pusat',
            'branch_address' => 'Jl. Sudirman No. 123, Jakarta Pusat'
        ];

        // Act
        $response = $this->postJson('/api/branches', $branchData);

        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'branch_code',
                    'branch_name',
                    'branch_address',
                    'created_at',
                    'updated_at'
                ])
                ->assertJson([
                    'branch_code' => 'BR001',
                    'branch_name' => 'Jakarta Pusat',
                    'branch_address' => 'Jl. Sudirman No. 123, Jakarta Pusat'
                ]);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('branches', $branchData);
    }

    /**
     * Test validasi saat membuat branch dengan data kosong
     */
    public function test_create_branch_validation_fails_with_empty_data()
    {
        // Act
        $response = $this->postJson('/api/branches', []);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'branch_code',
                    'branch_name',
                    'branch_address'
                ]);
    }

    /**
     * Test validasi saat membuat branch dengan data yang terlalu panjang
     */
    public function test_create_branch_validation_fails_with_too_long_data()
    {
        // Arrange
        $branchData = [
            'branch_code' => str_repeat('A', 256), // Lebih dari 255 karakter
            'branch_name' => str_repeat('B', 256),
            'branch_address' => str_repeat('C', 256)
        ];

        // Act
        $response = $this->postJson('/api/branches', $branchData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'branch_code',
                    'branch_name',
                    'branch_address'
                ]);
    }

    /**
     * Test untuk mendapatkan branch berdasarkan ID (GET /branches/{id})
     */
    public function test_can_get_branch_by_id()
    {
        // Arrange
        $branch = Branch::factory()->create([
            'branch_code' => 'BR002',
            'branch_name' => 'Bandung',
            'branch_address' => 'Jl. Asia Afrika No. 456, Bandung'
        ]);

        // Act
        $response = $this->getJson("/api/branches/{$branch->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'id' => $branch->id,
                    'branch_code' => 'BR002',
                    'branch_name' => 'Bandung',
                    'branch_address' => 'Jl. Asia Afrika No. 456, Bandung'
                ]);
    }

    /**
     * Test untuk mendapatkan branch yang tidak ada
     */
    public function test_get_branch_returns_404_when_not_found()
    {
        // Act
        $response = $this->getJson('/api/branches/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Branch not found'
                ]);
    }

    /**
     * Test untuk update branch (PUT/PATCH /branches/{id})
     */
    public function test_can_update_branch()
    {
        // Arrange
        $branch = Branch::factory()->create([
            'branch_code' => 'BR003',
            'branch_name' => 'Surabaya',
            'branch_address' => 'Jl. Pemuda No. 789, Surabaya'
        ]);

        $updateData = [
            'branch_code' => 'BR003-UPDATED',
            'branch_name' => 'Surabaya Utara',
            'branch_address' => 'Jl. Pemuda No. 789A, Surabaya Utara'
        ];

        // Act
        $response = $this->putJson("/api/branches/{$branch->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'branch_code',
                        'branch_name',
                        'branch_address',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'branch_code' => 'BR003-UPDATED',
                        'branch_name' => 'Surabaya Utara',
                        'branch_address' => 'Jl. Pemuda No. 789A, Surabaya Utara'
                    ]
                ]);

        // Verifikasi data terupdate di database
        $this->assertDatabaseHas('branches', array_merge(['id' => $branch->id], $updateData));
    }

    /**
     * Test update branch dengan data tidak valid
     */
    public function test_update_branch_validation_fails_with_invalid_data()
    {
        // Arrange
        $branch = Branch::factory()->create();

        // Act
        $response = $this->putJson("/api/branches/{$branch->id}", []);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'branch_code',
                    'branch_name',
                    'branch_address'
                ]);
    }

    /**
     * Test update branch yang tidak ada
     */
    public function test_update_branch_returns_404_when_not_found()
    {
        // Arrange
        $updateData = [
            'branch_code' => 'BR999',
            'branch_name' => 'Test Branch',
            'branch_address' => 'Test Address'
        ];

        // Act
        $response = $this->putJson('/api/branches/999', $updateData);

        // Assert
        $response->assertStatus(404);
    }

    /**
     * Test untuk menghapus branch (DELETE /branches/{id})
     */
    public function test_can_delete_branch()
    {
        // Arrange
        $branch = Branch::factory()->create();

        // Act
        $response = $this->deleteJson("/api/branches/{$branch->id}");

        // Assert
        $response->assertStatus(204);

        // Verifikasi data terhapus dari database
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    /**
     * Test untuk menghapus branch yang tidak ada
     */
    public function test_delete_branch_returns_404_when_not_found()
    {
        // Act
        $response = $this->deleteJson('/api/branches/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Branch not found'
                ]);
    }

    /**
     * Test untuk memastikan response format konsisten
     */
    public function test_response_format_consistency()
    {
        // Arrange
        $branch = Branch::factory()->create();

        // Act & Assert untuk GET single
        $response = $this->getJson("/api/branches/{$branch->id}");
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'branch_code',
                    'branch_name',
                    'branch_address',
                    'created_at',
                    'updated_at'
                ]);

        // Act & Assert untuk GET all
        $response = $this->getJson('/api/branches');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'branch_code',
                        'branch_name',
                        'branch_address',
                        'created_at',
                        'updated_at'
                    ]
                ]);
    }
}
