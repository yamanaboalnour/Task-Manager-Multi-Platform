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
        $attributes = $this->normalizeNames($attributes);

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

            $attributes = $this->normalizeNames($attributes, $target);
            $target->update($attributes);

            return $target->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeNames(array $attributes, ?User $target = null): array
    {
        if (array_key_exists('first_name', $attributes) || array_key_exists('last_name', $attributes)) {
            $firstName = $attributes['first_name'] ?? $target?->first_name ?? '';
            $lastName = $attributes['last_name'] ?? $target?->last_name ?? '';
            $attributes['first_name'] = $firstName;
            $attributes['last_name'] = $lastName;
            $attributes['name'] = trim($firstName.' '.$lastName);
        } elseif (isset($attributes['name'])) {
            $parts = preg_split('/\s+/u', trim($attributes['name']), 2) ?: [];
            $attributes['first_name'] = $parts[0] ?? '';
            $attributes['last_name'] = $parts[1] ?? '';
        }

        return $attributes;
    }
}
