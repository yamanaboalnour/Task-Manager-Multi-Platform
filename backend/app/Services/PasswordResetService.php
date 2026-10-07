<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * @param  array{email: string}  $credentials
     */
    public function sendResetLink(array $credentials): void
    {
        Password::sendResetLink($credentials);
    }

    /**
     * @param  array{email: string, token: string, password: string}  $attributes
     */
    public function reset(array $attributes): string
    {
        return Password::reset($attributes, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();

            event(new PasswordReset($user));
        });
    }
}
