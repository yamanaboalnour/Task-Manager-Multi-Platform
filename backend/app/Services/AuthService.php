<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function register(array $attributes): User
    {
        return User::create($attributes);
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function authenticate(array $credentials): ?User
    {
        $user = User::where('email', $credentials['email'])->first();

        return $user && Hash::check($credentials['password'], $user->password)
            ? $user
            : null;
    }
}
