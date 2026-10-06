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

        $query = $user->isManager()
            ? Task::query()->with('user:id,name,email')
            : $user->tasks();

        return $query->orderByDesc('updated_at')->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createFor(User $user, array $attributes): Task
    {
        Gate::forUser($user)->authorize('create', Task::class);

        $assignedUser = $user;
        if ($user->isManager() && isset($attributes['user_id'])) {
            $assignedUser = User::query()->findOrFail($attributes['user_id']);
        }

        unset($attributes['user_id']);

        return $assignedUser->tasks()->create($attributes)->load('user:id,name,email');
    }

    public function showFor(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('view', $task);

        return $task->load('user:id,name,email');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateFor(User $user, Task $task, array $attributes): Task
    {
        Gate::forUser($user)->authorize('update', $task);
        $task->update($attributes);

        return $task->load('user:id,name,email');
    }

    public function toggleCompletionFor(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('update', $task);
        $task->update(['is_completed' => ! $task->is_completed]);

        return $task->load('user:id,name,email');
    }

    public function deleteFor(User $user, Task $task): void
    {
        Gate::forUser($user)->authorize('delete', $task);
        $task->delete();
    }
}
