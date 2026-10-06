<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('dashboard', [
            'userCount' => User::query()->count(),
            'taskCount' => Task::query()->count(),
            'users' => User::query()->select(['id', 'name', 'email', 'role'])->withCount('tasks')->orderBy('name')->get(),
        ]);
    }
}
