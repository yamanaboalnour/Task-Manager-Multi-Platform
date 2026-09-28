@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <section class="auth-card">
        <h1>Welcome back</h1>
        <p class="muted">Log in to manage your tasks.</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>We could not log you in:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">

            <label>
                <input name="remember" type="checkbox" value="1" style="width:auto; margin-right:.35rem">
                Remember me
            </label>

            <button class="full" type="submit">Log in</button>
        </form>

        <p class="auth-footer muted">New to Task Manager? <a href="{{ route('register') }}">Create an account</a></p>
    </section>
@endsection
