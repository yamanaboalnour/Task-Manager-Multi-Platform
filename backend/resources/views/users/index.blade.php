@extends('layouts.app')

@section('title', __('User management'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ __('User management') }}</h1>
            <p class="muted">{{ __('Only managers can create accounts and change roles.') }}</p>
        </div>
    </div>
    @if ($errors->any())
        <div class="errors" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <section class="panel">
        <h2>{{ __('Add user') }}</h2>
        <form method="POST" action="{{ route('users.store') }}" class="form-grid">
            @csrf
            <div><label for="new-name">{{ __('Name') }}</label><input id="new-name" name="name" value="{{ old('name') }}" required maxlength="255"></div>
            <div><label for="new-email">{{ __('Email') }}</label><input id="new-email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required maxlength="255"></div>
            <div><label for="new-password">{{ __('Password') }}</label><input id="new-password" name="password" type="password" required minlength="8"></div>
            <div><label for="new-confirmation">{{ __('Confirm password') }}</label><input id="new-confirmation" name="password_confirmation" type="password" required></div>
            <div><label for="new-role">{{ __('Role') }}</label><select id="new-role" name="role"><option value="worker">{{ __('Worker') }}</option><option value="manager">{{ __('Manager') }}</option></select></div>
            <button type="submit">{{ __('Add user') }}</button>
        </form>
    </section>
    <section class="user-grid user-section">
        @foreach ($users as $user)
            <article class="panel">
                <h2>{{ $user->name }}</h2>
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <label for="name-{{ $user->id }}">{{ __('Name') }}</label>
                    <input id="name-{{ $user->id }}" name="name" value="{{ $user->name }}" required maxlength="255">
                    <label for="email-{{ $user->id }}">{{ __('Email') }}</label>
                    <input id="email-{{ $user->id }}" name="email" type="email" dir="ltr" value="{{ $user->email }}" required maxlength="255">
                    <label for="role-{{ $user->id }}">{{ __('Role') }}</label>
                    <select id="role-{{ $user->id }}" name="role">
                        <option value="worker" @selected($user->role === 'worker')>{{ __('Worker') }}</option>
                        <option value="manager" @selected($user->role === 'manager')>{{ __('Manager') }}</option>
                    </select>
                    <label for="password-{{ $user->id }}">{{ __('New password (optional)') }}</label>
                    <input id="password-{{ $user->id }}" name="password" type="password" minlength="8">
                    <label for="confirmation-{{ $user->id }}">{{ __('Confirm new password') }}</label>
                    <input id="confirmation-{{ $user->id }}" name="password_confirmation" type="password">
                    <button class="full" type="submit">{{ __('Save changes') }}</button>
                </form>
                <p class="muted">{{ $user->role === 'manager' ? __('Manager') : __('Worker') }} · {{ trans_choice('tasks.count', $user->tasks_count, ['count' => $user->tasks_count]) }}</p>
            </article>
        @endforeach
    </section>
@endsection
