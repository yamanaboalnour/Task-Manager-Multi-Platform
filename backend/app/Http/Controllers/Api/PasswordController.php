<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function forgot(Request $request, PasswordResetService $passwords)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $passwords->sendResetLink($validated);

        return response()->json([
            'message' => __('If the address belongs to an account, a password reset link has been sent.'),
        ], 202);
    }

    public function reset(ResetPasswordRequest $request, PasswordResetService $passwords)
    {
        $status = $passwords->reset($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __('Your password has been reset.'),
        ]);
    }
}
