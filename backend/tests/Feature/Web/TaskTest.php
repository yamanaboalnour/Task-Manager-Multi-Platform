<?php

namespace Tests\Feature\Web;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_complete_and_delete_a_task(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/tasks')->assertOk()->assertSee('Add a task');

        $this->post('/tasks', [
            'title' => 'Original web task',
            'description' => 'Created from Blade',
        ])->assertRedirect(route('tasks.index'));

        $task = Task::query()->where('title', 'Original web task')->firstOrFail();
        $this->assertSame($user->id, $task->user_id);

        $this->get('/tasks')
            ->assertOk()
            ->assertSee('Original web task')
            ->assertSee('Created from Blade');

        $this->put("/tasks/{$task->id}", [
            'title' => 'Updated web task',
            'description' => 'Updated description',
        ])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated web task',
            'description' => 'Updated description',
            'is_completed' => false,
        ]);

        $this->patch("/tasks/{$task->id}/complete")
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_completed' => true]);

        $this->patch("/tasks/{$task->id}/complete")
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_completed' => false]);

        $this->delete("/tasks/{$task->id}")
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_forms_display_validation_errors(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from('/tasks')
            ->post('/tasks', ['title' => ''])
            ->assertRedirect('/tasks')
            ->assertSessionHasErrors('title');

        $task = Task::factory()->create(['user_id' => auth()->id()]);

        $this->from('/tasks')
            ->put("/tasks/{$task->id}", ['title' => str_repeat('x', 256)])
            ->assertRedirect('/tasks')
            ->assertSessionHasErrors('title');
    }

    public function test_user_cannot_update_complete_or_delete_another_users_task(): void
    {
        $task = Task::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->put("/tasks/{$task->id}", ['title' => 'Unauthorized'])
            ->assertForbidden();
        $this->patch("/tasks/{$task->id}/complete")
            ->assertForbidden();
        $this->delete("/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $task->title,
            'is_completed' => false,
        ]);
    }

    public function test_guests_cannot_open_or_submit_task_pages(): void
    {
        $task = Task::factory()->create();

        $this->get('/tasks')->assertRedirect(route('login'));
        $this->post('/tasks', ['title' => 'No session'])->assertRedirect(route('login'));
        $this->put("/tasks/{$task->id}", ['title' => 'No session'])->assertRedirect(route('login'));
        $this->patch("/tasks/{$task->id}/complete")->assertRedirect(route('login'));
        $this->delete("/tasks/{$task->id}")->assertRedirect(route('login'));
    }
}
