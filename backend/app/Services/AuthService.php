<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
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
