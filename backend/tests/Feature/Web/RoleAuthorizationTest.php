<?php

namespace Tests\Feature\Web;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_gets_dashboard_user_management_and_all_tasks_grouped_by_owner(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $worker->id]);
        $this->actingAs($manager);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('لوحة التحكم')
            ->assertSee('إدارة المستخدمين')
            ->assertSee($worker->name);
        $this->get('/users')->assertOk()->assertSee('إضافة مستخدم')->assertSee($worker->email);
        $this->get('/tasks')->assertOk()->assertSee('جميع المهام')->assertSee($task->title);

        $this->post('/users', [
            'name' => 'Created Worker',
            'email' => 'created-worker@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_WORKER,
        ])->assertRedirect(route('users.index'));
        $newUser = User::query()->where('email', 'created-worker@example.test')->firstOrFail();

        $this->put("/users/{$newUser->id}", [
            'name' => 'Manager Created',
            'role' => User::ROLE_MANAGER,
        ])->assertRedirect(route('users.index'));
        $this->assertSame(User::ROLE_MANAGER, $newUser->fresh()->role);
    }

    public function test_worker_sees_only_own_tasks_and_cannot_open_management_pages(): void
    {
        $worker = User::factory()->create();
        $ownTask = Task::factory()->create(['user_id' => $worker->id]);
        $otherTask = Task::factory()->create();
        $this->actingAs($worker);

        $this->get('/tasks')
            ->assertOk()
            ->assertSee('مهامي')
            ->assertSee($ownTask->title)
            ->assertDontSee($otherTask->title);
        $this->get('/dashboard')
            ->assertForbidden()
            ->assertSee('ليس لديك صلاحية لتنفيذ هذا الإجراء.');
        $this->get('/users')
            ->assertForbidden()
            ->assertSee('ليس لديك صلاحية لتنفيذ هذا الإجراء.');
        $this->put("/tasks/{$otherTask->id}", ['title' => str_repeat('x', 256)])
            ->assertForbidden();
        $this->post('/users', [
            'name' => 'Forbidden User',
            'email' => 'forbidden@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'forbidden@example.test']);
    }

    public function test_registration_never_assigns_manager_role_from_web_input(): void
    {
        $this->post('/register', [
            'first_name' => 'Public',
            'last_name' => 'User',
            'email' => 'public-role@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'public-role@example.test']);
        $this->assertDatabaseHas('registration_requests', [
            'email' => 'public-role@example.test',
            'status' => 'pending',
        ]);
    }
}
