<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class TaskService
{
    /**
     * @return Collection<int, Task>
     */
    public function listFor(User $user): Collection
    {
        Gate::forUser($user)->authorize('viewAny', Task::class);

        return $user->tasks()->orderByDesc('updated_at')->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(User $user, array $attributes): Task
    {
        Gate::forUser($user)->authorize('create', Task::class);

        return $user->tasks()->create($attributes);
    }

    public function showFor(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('view', $task);

        return $task;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateFor(User $user, Task $task, array $attributes): Task
    {
        Gate::forUser($user)->authorize('update', $task);
        $task->update($attributes);

        return $task;
    }

    public function toggleCompletionFor(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('update', $task);
        $task->update(['is_completed' => ! $task->is_completed]);

        return $task;
    }

    public function deleteFor(User $user, Task $task): void
    {
        Gate::forUser($user)->authorize('delete', $task);
        $task->delete();
    }
}
