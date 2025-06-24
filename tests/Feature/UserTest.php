<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup tambahan jika diperlukan
    }

    /**
     * Test untuk mendapatkan semua user (GET /users)
     */
    public function test_can_get_all_users()
    {
        // Arrange - Buat branch dan users untuk testing
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        
        User::factory()->count(3)->create(['branch_id' => $branch1->id]);
        User::factory()->count(2)->create(['branch_id' => $branch2->id]);

        // Act - Panggil endpoint
        $response = $this->getJson('/api/users');

        // Assert - Verifikasi response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'branch' => [
                                'id',
                                'branch_code',
                                'branch_name',
                                'branch_address'
                            ],
                            'deleted_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'total'
                    ]
                ]);

        $this->assertEquals(5, $response->json('meta.total'));
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test untuk mendapatkan semua user ketika data kosong
     */
    public function test_can_get_empty_users_list()
    {
        // Act
        $response = $this->getJson('/api/users');

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [],
                    'meta' => [
                        'total' => 0
                    ]
                ]);
    }

    /**
     * Test untuk memastikan users diurutkan berdasarkan updated_at desc
     */
    public function test_users_ordered_by_updated_at_desc()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $user1 = User::factory()->create([
            'branch_id' => $branch->id,
            'updated_at' => now()->subDays(2)
        ]);
        $user2 = User::factory()->create([
            'branch_id' => $branch->id,
            'updated_at' => now()->subDay()
        ]);
        $user3 = User::factory()->create([
            'branch_id' => $branch->id,
            'updated_at' => now()
        ]);

        // Act
        $response = $this->getJson('/api/users');

        // Assert
        $users = $response->json('data');
        $this->assertEquals($user3->id, $users[0]['id']);
        $this->assertEquals($user2->id, $users[1]['id']);
        $this->assertEquals($user1->id, $users[2]['id']);
    }

    /**
     * Test untuk membuat user baru dengan role super_admin (POST /users)
     */
    public function test_can_create_new_user_super_admin()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $userData = [
            'branch_id' => $branch->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'super_admin'
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'branch' => [
                            'id',
                            'branch_code',
                            'branch_name',
                            'branch_address'
                        ],
                        'deleted_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'role' => 'super_admin',
                        'branch' => [
                            'id' => $branch->id,
                        ]
                    ]
                ]);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'super_admin',
            'branch_id' => $branch->id
        ]);

        // Verifikasi password ter-hash
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test untuk membuat user baru dengan role admin
     */
    public function test_can_create_new_user_admin()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $userData = [
            'branch_id' => $branch->id,
            'name' => 'Jane Admin',
            'email' => 'jane@example.com',
            'password' => 'securepass',
            'role' => 'admin'
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'name' => 'Jane Admin',
                        'email' => 'jane@example.com',
                        'role' => 'admin'
                    ]
                ]);
    }

    /**
     * Test validasi saat membuat user dengan data kosong
     */
    public function test_create_user_validation_fails_with_empty_data()
    {
        // Act
        $response = $this->postJson('/api/users', []);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'branch_id',
                    'name',
                    'email',
                    'password',
                    'role'
                ]);
    }

    /**
     * Test validasi saat membuat user dengan branch_id yang tidak ada
     */
    public function test_create_user_validation_fails_with_invalid_branch_id()
    {
        // Arrange
        $userData = [
            'branch_id' => 999,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['branch_id']);
    }

    /**
     * Test validasi email format dan unique
     */
    public function test_create_user_validation_fails_with_invalid_email()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $existingUser = User::factory()->create([
            'branch_id' => $branch->id,
            'email' => 'existing@example.com'
        ]);

        // Test email format tidak valid
        $userData1 = [
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/users', $userData1);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test email sudah ada
        $userData2 = [
            'branch_id' => $branch->id,
            'name' => 'Test User 2',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/users', $userData2);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test validasi password minimum 6 karakter
     */
    public function test_create_user_validation_fails_with_short_password()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $userData = [
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '12345', // Kurang dari 6 karakter
            'role' => 'admin'
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test validasi role yang tidak valid
     */
    public function test_create_user_validation_fails_with_invalid_role()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $userData = [
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'invalid_role'
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test untuk mendapatkan user berdasarkan ID (GET /users/{id})
     */
    public function test_can_get_user_by_id()
    {
        // Arrange
        $branch = Branch::factory()->create([
            'branch_code' => 'BR001',
            'branch_name' => 'Jakarta Branch'
        ]);
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        // Act
        $response = $this->getJson("/api/users/{$user->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $user->id,
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                        'role' => 'admin',
                        'branch' => [
                            'id' => $branch->id,
                        ]
                    ]
                ]);
    }

    /**
     * Test untuk mendapatkan user yang tidak ada
     */
    public function test_get_user_returns_404_when_not_found()
    {
        // Act
        $response = $this->getJson('/api/users/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'User not found'
                ]);
    }

    /**
     * Test untuk update user tanpa mengubah password (PUT /users/{id})
     */
    public function test_can_update_user_without_password()
    {
        // Arrange
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch1->id,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'admin'
        ]);

        $updateData = [
            'branch_id' => $branch2->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'role' => 'super_admin'
        ];

        // Act
        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $user->id,
                        'name' => 'New Name',
                        'email' => 'new@example.com',
                        'role' => 'super_admin',
                        'branch' => [
                            'id' => $branch2->id
                        ]
                    ]
                ]);

        // Verifikasi data terupdate di database
        $this->assertDatabaseHas('users', array_merge(['id' => $user->id], $updateData));
    }

    /**
     * Test untuk update user dengan mengubah password
     */
    public function test_can_update_user_with_password()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'password' => bcrypt('oldpassword')
        ]);

        $updateData = [
            'branch_id' => $branch->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'role' => $user->role
        ];

        // Act
        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        // Assert
        $response->assertStatus(200);

        // Verifikasi password baru ter-hash
        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
        $this->assertFalse(Hash::check('oldpassword', $updatedUser->password));
    }

    /**
     * Test update user dengan email yang sudah ada (kecuali milik sendiri)
     */
    public function test_update_user_allows_same_email_for_same_user()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $user1 = User::factory()->create([
            'branch_id' => $branch->id,
            'email' => 'user1@example.com'
        ]);
        $user2 = User::factory()->create([
            'branch_id' => $branch->id,
            'email' => 'user2@example.com'
        ]);

        // Test update dengan email sendiri (should pass)
        $updateData = [
            'branch_id' => $branch->id,
            'name' => $user1->name,
            'email' => 'user1@example.com', // Email yang sama
            'role' => $user1->role
        ];

        $response = $this->putJson("/api/users/{$user1->id}", $updateData);
        $response->assertStatus(200);

        // Test update dengan email user lain (should fail)
        $updateData2 = [
            'branch_id' => $branch->id,
            'name' => $user1->name,
            'email' => 'user2@example.com', // Email user lain
            'role' => $user1->role
        ];

        $response = $this->putJson("/api/users/{$user1->id}", $updateData2);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test update user yang tidak ada
     */
    public function test_update_user_returns_404_when_not_found()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $updateData = [
            'branch_id' => $branch->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ];

        // Act
        $response = $this->putJson('/api/users/999', $updateData);

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'User not found'
                ]);
    }

    /**
     * Test untuk menghapus user (DELETE /users/{id})
     */
    public function test_can_delete_user()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        // Act
        $response = $this->deleteJson("/api/users/{$user->id}");

        // Assert
        $response->assertStatus(204);

        // Verifikasi data terhapus dari database
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }

    /**
     * Test untuk menghapus user yang tidak ada
     */
    public function test_delete_user_returns_404_when_not_found()
    {
        // Act
        $response = $this->deleteJson('/api/users/999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'User not found'
                ]);
    }

    /**
     * Test untuk mendapatkan semua admin (GET /users/admins)
     */
    public function test_can_get_all_admins()
    {
        // Arrange
        $branch = Branch::factory()->create();
        
        // Buat users dengan berbagai role
        User::factory()->count(2)->create([
            'branch_id' => $branch->id,
            'role' => 'admin'
        ]);
        User::factory()->count(1)->create([
            'branch_id' => $branch->id,
            'role' => 'super_admin'
        ]);

        // Act
        $response = $this->getJson('/api/users/admins');

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'branch' => [
                                'id',
                                'branch_code',
                                'branch_name',
                                'branch_address'
                            ],
                            'deleted_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'total'
                    ]
                ]);

        $this->assertEquals(2, $response->json('meta.total'));
        $this->assertCount(2, $response->json('data'));

        // Verifikasi semua user yang dikembalikan adalah admin
        foreach ($response->json('data') as $user) {
            $this->assertEquals('admin', $user['role']);
        }
    }

    /**
     * Test untuk mendapatkan admin ketika tidak ada admin
     */
    public function test_can_get_empty_admins_list()
    {
        // Arrange - Buat user dengan role selain admin
        $branch = Branch::factory()->create();
        User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'super_admin'
        ]);

        // Act
        $response = $this->getJson('/api/users/admins');

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [],
                    'meta' => [
                        'total' => 0
                    ]
                ]);
    }

    /**
     * Test untuk memastikan admins diurutkan berdasarkan updated_at desc
     */
    public function test_admins_ordered_by_updated_at_desc()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $admin1 = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'admin',
            'updated_at' => now()->subDays(2)
        ]);
        $admin2 = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'admin',
            'updated_at' => now()->subDay()
        ]);
        $admin3 = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'admin',
            'updated_at' => now()
        ]);

        // Act
        $response = $this->getJson('/api/users/admins');

        // Assert
        $admins = $response->json('data');
        $this->assertEquals($admin3->id, $admins[0]['id']);
        $this->assertEquals($admin2->id, $admins[1]['id']);
        $this->assertEquals($admin1->id, $admins[2]['id']);
    }

    /**
     * Test untuk memastikan response menggunakan UserResource
     */
    public function test_response_uses_user_resource_format()
    {
        // Arrange
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        // Act
        $response = $this->getJson("/api/users/{$user->id}");

        // Assert - Pastikan structure sesuai UserResource
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'branch' => [
                            'id',
                            'branch_code',
                            'branch_name',
                            'branch_address'
                        ],
                        'deleted_at',
                        'updated_at'
                    ]
                ]);

        // Pastikan tidak ada password dalam response
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    /**
     * Test boundary values untuk validasi
     */
    public function test_boundary_values_validation()
    {
        $branch = Branch::factory()->create();

        // Test nama dengan 255 karakter (valid)
        $validData = [
            'branch_id' => $branch->id,
            'name' => str_repeat('A', 255),
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/users', $validData);
        $response->assertStatus(201);

        // Test nama dengan 256 karakter (invalid)
        $invalidData = [
            'branch_id' => $branch->id,
            'name' => str_repeat('A', 256),
            'email' => 'test2@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/users', $invalidData);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }
}
