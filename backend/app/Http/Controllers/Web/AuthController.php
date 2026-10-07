<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreRegistrationRequest;
use App\Services\AuthService;
use App\Services\RegistrationRequestService;
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
            ? redirect()->route(Auth::user()->isManager() ? 'dashboard' : 'tasks.index')
            : view('auth.register');
    }

    public function register(
        StoreRegistrationRequest $request,
        RegistrationRequestService $requests,
    ): RedirectResponse {
        $requests->submit($request->validated());

        return redirect()->route('login')->with('status', __('Your account request has been submitted for review.'));
    }

    public function createLogin(): View|RedirectResponse
    {
        return Auth::check()
            ? redirect()->route(Auth::user()->isManager() ? 'dashboard' : 'tasks.index')
            : view('auth.login');
    }

    public function login(LoginRequest $request, AuthService $auth): RedirectResponse
    {
        $user = $auth->authenticate($request->validated());

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->isManager() ? 'dashboard' : 'tasks.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('You have signed out.'));
    }
}
