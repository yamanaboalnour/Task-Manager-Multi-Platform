<?php

namespace Tests\Feature\Api;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_request_does_not_create_an_active_user_or_manager(): void
    {
        $response = $this->postJson('/api/v1/register-request', [
            'first_name' => 'New',
            'last_name' => 'Worker',
            'email' => 'new-worker@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ]);

        $response->assertCreated()
            ->assertJsonPath('registration_request.status', RegistrationRequest::STATUS_PENDING)
            ->assertJsonMissingPath('registration_request.password_hash');

        $this->assertDatabaseMissing('users', ['email' => 'new-worker@example.test']);
        $this->assertDatabaseHas('registration_requests', [
            'email' => 'new-worker@example.test',
            'status' => RegistrationRequest::STATUS_PENDING,
        ]);
        $this->assertTrue(Hash::check(
            'password123',
            RegistrationRequest::query()->firstOrFail()->password_hash
        ));

        $this->postJson('/api/v1/login', [
            'email' => 'new-worker@example.test',
            'password' => 'password123',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/register', [
            'name' => 'Bypass',
            'email' => 'bypass@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }

    public function test_registration_request_rejects_duplicate_pending_and_existing_emails(): void
    {
        $requestData = [
            'first_name' => 'First',
            'last_name' => 'Person',
            'email' => 'duplicate@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->postJson('/api/v1/register-request', $requestData)->assertCreated();
        $this->postJson('/api/v1/register-request', $requestData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        User::factory()->create(['email' => 'active@example.test']);
        $this->postJson('/api/v1/register-request', [
            ...$requestData,
            'email' => 'active@example.test',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_manager_can_approve_a_request_and_the_account_is_a_worker(): void
    {
        $manager = User::factory()->manager()->create();
        $registrationRequest = RegistrationRequest::factory()->create([
            'first_name' => 'Approved',
            'last_name' => 'Worker',
            'email' => 'approved@example.test',
            'password_hash' => Hash::make('password123'),
        ]);
        $token = $manager->createToken('manager')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/registration-requests')
            ->assertOk()
            ->assertJsonPath('0.email', 'approved@example.test')
            ->assertJsonMissingPath('0.password_hash');

        $this->withToken($token)
            ->postJson("/api/v1/registration-requests/{$registrationRequest->id}/approve")
            ->assertOk()
            ->assertJsonPath('registration_request.status', RegistrationRequest::STATUS_APPROVED)
            ->assertJsonPath('registration_request.user.role', User::ROLE_WORKER);

        $user = User::query()->where('email', 'approved@example.test')->firstOrFail();
        $this->assertSame('Approved Worker', $user->name);
        $this->assertSame('Approved', $user->first_name);
        $this->assertSame('Worker', $user->last_name);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNull($registrationRequest->fresh()->password_hash);

        $this->postJson('/api/v1/login', [
            'email' => 'approved@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.role', User::ROLE_WORKER);
    }

    public function test_manager_can_reject_request_without_creating_a_user(): void
    {
        $manager = User::factory()->manager()->create();
        $registrationRequest = RegistrationRequest::factory()->create([
            'email' => 'rejected@example.test',
            'password_hash' => Hash::make('password123'),
        ]);

        $this->withToken($manager->createToken('manager')->plainTextToken)
            ->postJson("/api/v1/registration-requests/{$registrationRequest->id}/reject")
            ->assertOk()
            ->assertJsonPath('registration_request.status', RegistrationRequest::STATUS_REJECTED);

        $this->assertDatabaseMissing('users', ['email' => 'rejected@example.test']);
        $this->assertNull($registrationRequest->fresh()->password_hash);
        $this->postJson('/api/v1/login', [
            'email' => 'rejected@example.test',
            'password' => 'password123',
        ])->assertUnprocessable();
    }

    public function test_worker_cannot_view_or_review_registration_requests(): void
    {
        $worker = User::factory()->create();
        $registrationRequest = RegistrationRequest::factory()->create();
        $token = $worker->createToken('worker')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/registration-requests')
            ->assertForbidden();
        $this->withToken($token)
            ->postJson("/api/v1/registration-requests/{$registrationRequest->id}/approve")
            ->assertForbidden();
        $this->withToken($token)
            ->postJson("/api/v1/registration-requests/{$registrationRequest->id}/reject")
            ->assertForbidden();
    }

    public function test_forgot_password_is_non_enumerating_and_valid_token_resets_password(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'reset@example.test',
            'password' => 'old-password',
        ]);
        $oldToken = $user->createToken('old-device')->plainTextToken;

        $known = $this->postJson('/api/v1/forgot-password', ['email' => $user->email]);
        $unknown = $this->postJson('/api/v1/forgot-password', ['email' => 'missing@example.test']);
        $known->assertAccepted()->assertExactJson($unknown->json());
        Notification::assertSentTo($user, ResetPassword::class);

        $resetToken = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$resetToken): bool {
            $resetToken = $notification->token;

            return true;
        });

        $this->postJson('/api/v1/reset-password', [
            'email' => $user->email,
            'token' => $resetToken,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertOk();

        Auth::forgetGuards();
        $this->withToken($oldToken)->getJson('/api/v1/me')->assertUnauthorized();
        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'new-password123',
        ])->assertOk();
        $this->postJson('/api/v1/reset-password', [
            'email' => $user->email,
            'token' => $resetToken,
            'password' => 'another-password123',
            'password_confirmation' => 'another-password123',
        ])->assertUnprocessable();
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

        $login->assertOk()
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
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'بيانات تسجيل الدخول غير صحيحة.');
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

    public function test_arabic_is_the_default_application_locale(): void
    {
        $this->assertSame('ar', app()->getLocale());
    }
}
