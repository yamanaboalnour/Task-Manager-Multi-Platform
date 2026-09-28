<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, TaskService $tasks)
    {
        return response()->json($tasks->listFor($request->user()));
    }

    public function store(StoreTaskRequest $request, TaskService $tasks)
    {
        $task = $tasks->createFor($request->user(), $request->validated());

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task, TaskService $tasks)
    {
        return response()->json($tasks->showFor($request->user(), $task));
    }

    public function update(UpdateTaskRequest $request, Task $task, TaskService $tasks)
    {
        return response()->json($tasks->updateFor($request->user(), $task, $request->validated()));
    }

    public function destroy(Request $request, Task $task, TaskService $tasks)
    {
        $tasks->deleteFor($request->user(), $task);

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    public function complete(Request $request, Task $task, TaskService $tasks)
    {
        return response()->json($tasks->toggleCompletionFor($request->user(), $task));
    }
}
