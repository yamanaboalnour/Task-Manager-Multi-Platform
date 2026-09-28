<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request, TaskService $tasks): View
    {
        return view('tasks.index', [
            'tasks' => $tasks->listFor($request->user()),
        ]);
    }

    public function store(StoreTaskRequest $request, TaskService $tasks): RedirectResponse
    {
        $tasks->createFor($request->user(), $request->validated());

        return redirect()->route('tasks.index')->with('status', 'Task added.');
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task,
        TaskService $tasks,
    ): RedirectResponse {
        $tasks->updateFor($request->user(), $task, $request->validated());

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function toggleCompletion(Request $request, Task $task, TaskService $tasks): RedirectResponse
    {
        $tasks->toggleCompletionFor($request->user(), $task);

        return redirect()->route('tasks.index')->with('status', 'Task status updated.');
    }

    public function destroy(Request $request, Task $task, TaskService $tasks): RedirectResponse
    {
        $tasks->deleteFor($request->user(), $task);

        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }
}
