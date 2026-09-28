<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->tasks()->orderByDesc('updated_at')->get()
        );
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return response()->json($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    public function complete(Task $task)
    {
        $this->authorize('update', $task);

        $task->update(['is_completed' => ! $task->is_completed]);

        return response()->json($task);
    }
}
