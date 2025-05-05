<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_login_with_valid_credentials()
    {
        // 1. Buat branch terlebih dahulu
        $branch = \App\Models\Branch::factory()->create();
        
        // 2. Buat user dengan branch_id yang valid
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'branch_id' => $branch->id,
            'role' => 'super_admin'
        ]);
        
        // 3. Test login
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function login_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'dummy',
        ]);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Email not found']);
    }

    /** @test */
    public function login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Password incorrect']);
    }
}