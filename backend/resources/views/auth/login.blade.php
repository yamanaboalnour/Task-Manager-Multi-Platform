@extends('layouts.app')

@section('title', __('Log in'))

@section('content')
    <section class="auth-card">
        <h1>{{ __('Welcome back') }}</h1>
        <p class="muted">{{ __('Log in to manage your tasks.') }}</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>{{ __('We could not log you in:') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required autocomplete="email" autofocus>

            <label for="password">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required autocomplete="current-password">

            <button class="full" type="submit">{{ __('Log in') }}</button>
        </form>

        <p class="auth-footer"><a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a></p>
        <p class="auth-footer muted">{{ __('New to Task Manager?') }} <a href="{{ route('register') }}">{{ __('Request an account') }}</a></p>
    </section>
@endsection
