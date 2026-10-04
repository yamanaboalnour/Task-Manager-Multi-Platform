@extends('layouts.app')

@section('title', __('Create account'))

@section('content')
    <section class="auth-card">
        <h1>{{ __('Create your account') }}</h1>
        <p class="muted">{{ __('Your tasks stay synced across your devices.') }}</p>

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
            <label for="name">{{ __('Name') }}</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>

            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required maxlength="255" autocomplete="email">

            <label for="password">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required minlength="8" autocomplete="new-password">

            <label for="password_confirmation">{{ __('Confirm password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required minlength="8" autocomplete="new-password">

            <button class="full" type="submit">{{ __('Create account') }}</button>
        </form>

        <p class="auth-footer muted">{{ __('Already registered?') }} <a href="{{ route('login') }}">{{ __('Log in') }}</a></p>
    </section>
@endsection
