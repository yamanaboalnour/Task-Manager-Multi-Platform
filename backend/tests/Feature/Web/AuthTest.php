<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_registration_and_register_into_a_session(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your account');

        $this->post('/register', [
            'name' => 'Web User',
            'email' => 'web-user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('tasks.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'web-user@example.test']);
        $this->get('/tasks')->assertOk()->assertSee('My tasks');
    }

    public function test_registration_displays_validation_errors(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_login_establishes_a_session_and_logout_invalidates_it(): void
    {
        User::factory()->create([
            'email' => 'web-login@example.test',
            'password' => 'password123',
        ]);

        $this->get('/login')->assertOk()->assertSee('Welcome back');
        $this->post('/login', [
            'email' => 'web-login@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('tasks.index'));

        $this->assertAuthenticated();
        $this->get('/tasks')->assertOk();

        $this->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->get('/tasks')->assertRedirect(route('login'));
    }

    public function test_invalid_credentials_return_to_login_with_an_error(): void
    {
        User::factory()->create([
            'email' => 'web-login@example.test',
            'password' => 'password123',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'web-login@example.test',
                'password' => 'incorrect',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_are_redirected_away_from_guest_auth_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect(route('tasks.index'));
        $this->get('/register')->assertRedirect(route('tasks.index'));
    }
}
