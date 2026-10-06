<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('users.index', [
            'users' => User::query()->select(['id', 'name', 'email', 'role'])->withCount('tasks')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request, UserManagementService $users): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $users->create($request->validated());

        return redirect()->route('users.index')->with('status', __('User created successfully.'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UserManagementService $users,
    ): RedirectResponse {
        Gate::authorize('update', $user);
        $users->update($user, $request->validated());

        return redirect()->route('users.index')->with('status', __('User updated successfully.'));
    }
}
