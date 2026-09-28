@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <section class="auth-card">
        <h1>Create your account</h1>
        <p class="muted">Your tasks stay synced across your devices.</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">

            <button class="full" type="submit">Create account</button>
        </form>

        <p class="auth-footer muted">Already registered? <a href="{{ route('login') }}">Log in</a></p>
    </section>
@endsection
