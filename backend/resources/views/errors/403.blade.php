@extends('layouts.app')

@section('title', __('Forbidden'))

@section('content')
    <section class="panel empty" role="alert">
        <h1>{{ __('Forbidden') }}</h1>
        <p>{{ $message }}</p>
        @auth
            <a class="button" href="{{ route('tasks.index') }}">{{ __('Return to tasks') }}</a>
        @endauth
    </section>
@endsection
