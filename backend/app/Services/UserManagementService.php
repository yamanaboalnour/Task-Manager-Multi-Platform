<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $target, array $attributes): User
    {
        return DB::transaction(function () use ($target, $attributes): User {
            if (
                $target->isManager()
                && ($attributes['role'] ?? User::ROLE_MANAGER) === User::ROLE_WORKER
                && ! User::query()
                    ->where('role', User::ROLE_MANAGER)
                    ->whereKeyNot($target->getKey())
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'role' => [__('At least one manager account must remain.')],
                ]);
            }

            if (empty($attributes['password'])) {
                unset($attributes['password']);
            }

            $target->update($attributes);

            return $target->refresh();
        });
    }
}
