@extends('layouts.app')

@section('title', __('Request an account'))

@section('content')
    <section class="auth-card">
        <h1>{{ __('Request an account') }}</h1>
        <p class="muted">{{ __('A manager must approve your request before you can sign in.') }}</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>{{ __('Please correct the following:') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="first_name">{{ __('First name') }}</label>
            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required maxlength="255" autocomplete="given-name" autofocus>

            <label for="last_name">{{ __('Last name') }}</label>
            <input id="last_name" name="last_name" value="{{ old('last_name') }}" required maxlength="255" autocomplete="family-name">

            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required maxlength="255" autocomplete="email">

            <label for="password">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required minlength="8" autocomplete="new-password">

            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required minlength="8" autocomplete="new-password">

            <button class="full" type="submit">{{ __('Submit account request') }}</button>
        </form>

        <p class="auth-footer muted">{{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Log in') }}</a></p>
    </section>
@endsection
