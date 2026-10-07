<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function createForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, PasswordResetService $passwords): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $passwords->sendResetLink($validated);

        return back()->with(
            'status',
            __('If the address belongs to an account, a password reset link has been sent.')
        );
    }

    public function createReset(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    public function reset(
        ResetPasswordRequest $request,
        PasswordResetService $passwords,
    ): RedirectResponse {
        $status = $passwords->reset($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return redirect()->route('login')->with('status', __('Your password has been reset.'));
    }
}
