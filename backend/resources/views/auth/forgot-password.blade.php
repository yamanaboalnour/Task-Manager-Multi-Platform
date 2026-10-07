@extends('layouts.app')

@section('title', __('Forgot your password?'))

@section('content')
    <section class="auth-card">
        <h1>{{ __('Forgot your password?') }}</h1>
        <p class="muted">{{ __('Enter your email address and we will send you a secure reset link.') }}</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required autocomplete="email" autofocus>
            <button class="full" type="submit">{{ __('Send reset link') }}</button>
        </form>

        <p class="auth-footer muted"><a href="{{ route('login') }}">{{ __('Back to login') }}</a></p>
    </section>
@endsection
