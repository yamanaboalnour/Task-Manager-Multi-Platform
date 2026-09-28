<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_task_endpoints(): void
    {
        $task = Task::factory()->create();

        $this->getJson('/api/v1/tasks')->assertUnauthorized();
        $this->postJson('/api/v1/tasks', ['title' => 'Task'])->assertUnauthorized();
        $this->getJson("/api/v1/tasks/{$task->id}")->assertUnauthorized();
        $this->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Updated'])->assertUnauthorized();
        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertUnauthorized();
        $this->patchJson("/api/v1/tasks/{$task->id}/complete")->assertUnauthorized();
    }

    public function test_user_can_create_list_show_update_toggle_and_delete_their_tasks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $created = $this->postJson('/api/v1/tasks', [
            'title' => 'Write API tests',
            'description' => 'Cover the task lifecycle',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('title', 'Write API tests')
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('is_completed', false);

        $taskId = $created->json('id');

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $taskId);

        $this->getJson("/api/v1/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('description', 'Cover the task lifecycle');

        $this->putJson("/api/v1/tasks/{$taskId}", [
            'title' => 'Updated API tests',
            'is_completed' => true,
        ])
            ->assertOk()
            ->assertJsonPath('title', 'Updated API tests')
            ->assertJsonPath('is_completed', true);

        $this->patchJson("/api/v1/tasks/{$taskId}/complete")
            ->assertOk()
            ->assertJsonPath('is_completed', false);

        $this->deleteJson("/api/v1/tasks/{$taskId}")->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_task_creation_validates_the_payload(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $this->postJson('/api/v1/tasks', [
            'title' => '',
            'is_completed' => 'not-a-boolean',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'is_completed']);
    }

    public function test_task_update_validates_fields_and_rejects_unknown_ownership_fields(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $this->putJson("/api/v1/tasks/{$task->id}", [
            'title' => str_repeat('x', 256),
            'is_completed' => 'invalid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'is_completed']);

        $this->putJson("/api/v1/tasks/{$task->id}", [
            'user_id' => User::factory()->create()->id,
        ])->assertOk();

        $this->assertSame($user->id, $task->fresh()->user_id);
    }

    public function test_user_cannot_read_update_complete_or_delete_another_users_task(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create(), 'sanctum');

        $this->getJson("/api/v1/tasks/{$task->id}")->assertForbidden();
        $this->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Hijacked'])->assertForbidden();
        $this->patchJson("/api/v1/tasks/{$task->id}/complete")->assertForbidden();
        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $task->title,
            'is_completed' => false,
        ]);
    }

    public function test_task_list_only_contains_the_authenticated_users_tasks(): void
    {
        $user = User::factory()->create();
        $ownedTask = Task::factory()->create(['user_id' => $user->id]);
        Task::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownedTask->id);
    }

    public function test_ownership_policy_accepts_numeric_ids_returned_as_strings(): void
    {
        $user = User::factory()->make(['id' => 12]);
        $task = new Task(['user_id' => '12']);

        $this->assertTrue(app(TaskPolicy::class)->view($user, $task));
    }
}
