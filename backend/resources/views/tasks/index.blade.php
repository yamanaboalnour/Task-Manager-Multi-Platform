@extends('layouts.app')

@section('title', 'My tasks')

@section('content')
    <div class="page-heading">
        <div>
            <h1>My tasks</h1>
            <p class="muted">Manage your work from one shared account.</p>
        </div>
        <span class="muted">{{ $tasks->count() }} {{ \Illuminate\Support\Str::plural('task', $tasks->count()) }}</span>
    </div>

    <div class="task-grid">
        <section class="panel" aria-labelledby="new-task-heading">
            <h2 id="new-task-heading">Add a task</h2>
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
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <label for="new-title">Title</label>
                <input id="new-title" name="title" value="{{ old('title') }}" required maxlength="255">
                <label for="new-description">Description</label>
                <textarea id="new-description" name="description" maxlength="5000">{{ old('description') }}</textarea>
                <button class="full" type="submit">Add task</button>
            </form>
        </section>

        <section class="task-list" aria-label="Your tasks">
            @forelse ($tasks as $task)
                <article class="task-card">
                    <div class="task-meta">
                        <span>{{ $task->is_completed ? 'Completed' : 'Active' }}</span>
                        <time datetime="{{ $task->created_at->toIso8601String() }}">Created {{ $task->created_at->format('M j, Y') }}</time>
                    </div>
                    <form method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PUT')
                        <label for="title-{{ $task->id }}">Title</label>
                        <input id="title-{{ $task->id }}" name="title" value="{{ $task->title }}" required maxlength="255">
                        <label for="description-{{ $task->id }}">Description</label>
                        <textarea id="description-{{ $task->id }}" name="description" maxlength="5000">{{ $task->description }}</textarea>
                        <div class="task-actions">
                            <button class="button-small" type="submit">Save changes</button>
                        </div>
                    </form>
                    <div class="task-actions">
                        <form method="POST" action="{{ route('tasks.complete', $task) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button-secondary button-small" type="submit">
                                {{ $task->is_completed ? 'Mark active' : 'Mark complete' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button-danger button-small" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="panel empty">You do not have any tasks yet. Add one using the form.</div>
            @endforelse
        </section>
    </div>
@endsection
