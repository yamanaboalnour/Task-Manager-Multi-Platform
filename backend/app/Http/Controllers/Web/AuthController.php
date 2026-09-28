<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function createRegistration(): View|RedirectResponse
    {
        return Auth::check()
            ? redirect()->route('tasks.index')
            : view('auth.register');
    }

    public function register(RegisterRequest $request, AuthService $auth): RedirectResponse
    {
        $user = $auth->register($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tasks.index');
    }

    public function createLogin(): View|RedirectResponse
    {
        return Auth::check()
            ? redirect()->route('tasks.index')
            : view('auth.login');
    }

    public function login(LoginRequest $request, AuthService $auth): RedirectResponse
    {
        $user = $auth->authenticate($request->validated());

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('tasks.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have signed out.');
    }
}
