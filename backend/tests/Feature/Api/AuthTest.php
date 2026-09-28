<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'new-user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'new-user@example.test')
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'new-user@example.test']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_validates_required_and_unique_fields(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => 'existing@example.test',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_and_access_their_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.test',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/v1/login', [
            'email' => 'login@example.test',
            'password' => 'password123',
        ]);

        $login
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['user', 'token']);

        $this->withToken($login->json('token'))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'login@example.test');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.test',
            'password' => 'password123',
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'login@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk();

        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->get('/api/v1/me')->assertUnauthorized()->assertJson(['message' => 'Unauthenticated.']);
    }
}
