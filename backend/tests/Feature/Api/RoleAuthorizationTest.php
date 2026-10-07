<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_administration_requires_authentication(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
        $this->postJson('/api/v1/users', [])->assertUnauthorized();
    }

    public function test_public_registration_request_cannot_assign_manager_role(): void
    {
        $this->postJson('/api/v1/register-request', [
            'first_name' => 'Untrusted',
            'last_name' => 'User',
            'email' => 'untrusted@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertCreated()
            ->assertJsonPath('registration_request.status', 'pending');

        $this->assertDatabaseMissing('users', ['email' => 'untrusted@example.test']);
    }

    public function test_trusted_console_command_can_bootstrap_an_existing_manager(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:make-manager', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertSame(User::ROLE_MANAGER, $user->fresh()->role);
    }

    public function test_manager_can_list_create_and_update_users_and_roles(): void
    {
        $manager = User::factory()->manager()->create();
        $this->actingAs($manager, 'sanctum');

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.role', User::ROLE_MANAGER);
        $this->getJson("/api/v1/users/{$manager->id}")
            ->assertOk()
            ->assertJsonPath('role', User::ROLE_MANAGER);

        $created = $this->postJson('/api/v1/users', [
            'name' => 'New Worker',
            'email' => 'worker@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_WORKER,
        ])->assertCreated()
            ->assertJsonPath('role', User::ROLE_WORKER);

        $workerId = $created->json('id');

        $this->putJson("/api/v1/users/{$workerId}", [
            'name' => 'Promoted Worker',
            'role' => User::ROLE_MANAGER,
        ])->assertOk()
            ->assertJsonPath('name', 'Promoted Worker')
            ->assertJsonPath('role', User::ROLE_MANAGER);

        $this->assertDatabaseHas('users', [
            'id' => $workerId,
            'role' => User::ROLE_MANAGER,
            'name' => 'Promoted Worker',
        ]);
    }

    public function test_manager_cannot_demote_the_last_manager(): void
    {
        $manager = User::factory()->manager()->create();
        $this->actingAs($manager, 'sanctum')
            ->patchJson("/api/v1/users/{$manager->id}", ['role' => User::ROLE_WORKER])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertSame(User::ROLE_MANAGER, $manager->fresh()->role);
    }

    public function test_worker_cannot_list_create_or_change_users(): void
    {
        $worker = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($worker, 'sanctum');

        $this->getJson('/api/v1/users')
            ->assertForbidden()
            ->assertJsonPath('message', 'ليس لديك صلاحية لتنفيذ هذا الإجراء.');
        $this->getJson("/api/v1/users/{$other->id}")->assertForbidden();
        $this->postJson('/api/v1/users', [
            'name' => 'Escalation Attempt',
            'email' => 'manager-attempt@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertForbidden();
        $this->patchJson("/api/v1/users/{$worker->id}", ['role' => User::ROLE_MANAGER])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'manager-attempt@example.test']);
        $this->assertSame(User::ROLE_WORKER, $worker->fresh()->role);
    }

    public function test_manager_can_view_and_manage_tasks_belonging_to_any_user(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $workerTask = Task::factory()->create(['user_id' => $worker->id]);
        $this->actingAs($manager, 'sanctum');

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $workerTask->id)
            ->assertJsonPath('0.user.name', $worker->name);

        $assigned = $this->postJson('/api/v1/tasks', [
            'title' => 'Assigned by manager',
            'user_id' => $worker->id,
        ])->assertCreated()
            ->assertJsonPath('user_id', $worker->id);

        $taskId = $workerTask->id;
        $this->getJson("/api/v1/tasks/{$taskId}")->assertOk();
        $this->putJson("/api/v1/tasks/{$taskId}", ['title' => 'Manager update'])
            ->assertOk()
            ->assertJsonPath('title', 'Manager update')
            ->assertJsonPath('user.name', $worker->name);
        $this->patchJson("/api/v1/tasks/{$taskId}/complete")
            ->assertOk()->assertJsonPath('is_completed', true);
        $this->deleteJson("/api/v1/tasks/{$taskId}")->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $assigned->json('id'), 'user_id' => $worker->id]);
    }

    public function test_worker_only_sees_and_manages_owned_tasks_and_cannot_assign_another_owner(): void
    {
        $worker = User::factory()->create();
        $otherTask = Task::factory()->create();
        $ownedTask = Task::factory()->create(['user_id' => $worker->id]);
        $this->actingAs($worker, 'sanctum');

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownedTask->id);

        $this->getJson("/api/v1/tasks/{$otherTask->id}")->assertForbidden();
        $this->putJson("/api/v1/tasks/{$otherTask->id}", ['title' => 'Forbidden'])->assertForbidden();
        $this->putJson("/api/v1/tasks/{$otherTask->id}", ['title' => str_repeat('x', 256)])
            ->assertForbidden();
        $this->patchJson("/api/v1/tasks/{$otherTask->id}/complete")->assertForbidden();
        $this->deleteJson("/api/v1/tasks/{$otherTask->id}")->assertForbidden();
        $this->postJson('/api/v1/tasks', [
            'title' => 'Spoofed ownership',
            'user_id' => $otherTask->user_id,
        ])->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id, 'title' => $otherTask->title]);
    }
}
