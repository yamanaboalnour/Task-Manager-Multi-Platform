<?php

namespace Tests\Feature\Web;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_registration_request_without_being_logged_in(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('طلب إنشاء حساب')
            ->assertSee('lang="ar" dir="rtl"', false)
            ->assertSee('first_name')
            ->assertSee('last_name');

        $this->post('/register', [
            'first_name' => 'Web',
            'last_name' => 'Worker',
            'email' => 'web-worker@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'web-worker@example.test']);
        $this->assertDatabaseHas('registration_requests', [
            'email' => 'web-worker@example.test',
            'status' => RegistrationRequest::STATUS_PENDING,
        ]);
    }

    public function test_registration_displays_arabic_validation_errors(): void
    {
        $this->followingRedirects()
            ->from('/register')
            ->post('/register', [
                'first_name' => '',
                'last_name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertOk()
            ->assertSee('حقل الاسم الأول مطلوب.')
            ->assertSee('حقل اسم العائلة مطلوب.');
        $this->assertGuest();
    }

    public function test_manager_can_review_registration_request_and_worker_is_denied(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $registrationRequest = RegistrationRequest::factory()->create([
            'first_name' => 'Approved',
            'last_name' => 'Worker',
            'email' => 'web-approved@example.test',
            'password_hash' => Hash::make('password123'),
        ]);

        $this->actingAs($worker)
            ->get('/registration-requests')
            ->assertForbidden();

        $this->actingAs($manager)
            ->get('/registration-requests')
            ->assertOk()
            ->assertSee('طلبات إنشاء الحسابات')
            ->assertSee('web-approved@example.test');

        $this->actingAs($manager)
            ->post("/registration-requests/{$registrationRequest->id}/approve")
            ->assertRedirect(route('registration-requests.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'web-approved@example.test',
            'role' => User::ROLE_WORKER,
        ]);
    }

    public function test_login_establishes_a_session_and_logout_invalidates_it(): void
    {
        User::factory()->create([
            'email' => 'web-login@example.test',
            'password' => 'password123',
        ]);

        $this->get('/login')->assertOk()->assertSee('مرحبًا بعودتك');
        $this->post('/login', [
            'email' => 'web-login@example.test',
            'password' => 'password123',
            'remember' => '1',
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

        $this->followingRedirects()
            ->from('/login')
            ->post('/login', [
                'email' => 'web-login@example.test',
                'password' => 'incorrect',
            ])
            ->assertOk()
            ->assertSee('بيانات تسجيل الدخول غير صحيحة.');
        $this->assertGuest();
    }

    public function test_forgot_password_uses_reset_token_and_page(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'forgot@example.test']);

        $this->get('/forgot-password')->assertOk()->assertSee('نسيت كلمة المرور؟');
        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('إنشاء كلمة مرور جديدة');
    }

    public function test_authenticated_users_are_redirected_away_from_guest_auth_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect(route('tasks.index'));
        $this->get('/register')->assertRedirect(route('tasks.index'));
    }
}
