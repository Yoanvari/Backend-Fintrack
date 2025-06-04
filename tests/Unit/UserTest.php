<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_factory()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_has_correct_fillable_attributes()
    {
        $user = new User();

        $this->assertEquals([
            'branch_id',
            'name',
            'email',
            'password',
            'role',
        ], $user->getFillable());
    }

    public function test_user_hides_password_and_remember_token_in_array()
    {
        $user = User::factory()->make();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_user_belongs_to_a_branch()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Branch::class, $user->branch);
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create(['password' => 'secret']);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret', $user->password));
    }
}
