<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return response()->json(
            User::query()->select(['id', 'name', 'email', 'role', 'created_at'])->withCount('tasks')->orderBy('name')->get()
        );
    }

    public function store(StoreUserRequest $request, UserManagementService $users)
    {
        Gate::authorize('create', User::class);

        return response()->json(
            $users->create($request->validated()),
            201,
        );
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        return response()->json($user);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UserManagementService $users,
    ) {
        Gate::authorize('update', $user);

        return response()->json($users->update($user, $request->validated()));
    }
}
