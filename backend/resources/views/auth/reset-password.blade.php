@extends('layouts.app')

@section('title', __('Create a new password'))

@section('content')
    <section class="auth-card">
        <h1>{{ __('Create a new password') }}</h1>

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $email) }}" required autocomplete="email">
            <label for="password">{{ __('New password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required minlength="8" autocomplete="new-password">
            <label for="password_confirmation">{{ __('Confirm new password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required minlength="8" autocomplete="new-password">
            <button class="full" type="submit">{{ __('Reset password') }}</button>
        </form>
    </section>
@endsection
