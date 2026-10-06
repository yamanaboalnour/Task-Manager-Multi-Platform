@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ __('Dashboard') }}</h1>
            <p class="muted">{{ __('Manage users and tasks across the workspace.') }}</p>
        </div>
    </div>
    <div class="summary-grid">
        <section class="panel summary-card">
            <h2>{{ __('Users') }}</h2>
            <p>{{ trans_choice('users.count', $userCount, ['count' => $userCount]) }}</p>
            <a class="button button-small" href="{{ route('users.index') }}">{{ __('Manage users') }}</a>
        </section>
        <section class="panel summary-card">
            <h2>{{ __('All tasks') }}</h2>
            <p>{{ trans_choice('tasks.count', $taskCount, ['count' => $taskCount]) }}</p>
            <a class="button button-small" href="{{ route('tasks.index') }}">{{ __('Manage tasks') }}</a>
        </section>
    </div>
    <section class="panel user-section">
        <h2>{{ __('Users and their tasks') }}</h2>
        <div class="user-grid">
            @foreach ($users as $user)
                <article class="task-card">
                    <div class="user-heading">
                        <strong>{{ $user->name }}</strong>
                        <span class="muted">{{ $user->role === 'manager' ? __('Manager') : __('Worker') }}</span>
                    </div>
                    <p class="muted">{{ $user->email }}</p>
                    <p>{{ trans_choice('tasks.count', $user->tasks_count, ['count' => $user->tasks_count]) }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
