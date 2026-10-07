@extends('layouts.app')

@section('title', __('Surveys'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ __('Surveys') }}</h1>
            <p class="muted">{{ __('Create and answer surveys in one place.') }}</p>
        </div>
        @if (auth()->user()->isManager())
            <a class="button" href="{{ route('surveys.create') }}">{{ __('Create a survey') }}</a>
        @endif
    </div>

    <div class="user-grid">
        @forelse ($surveys as $survey)
            <article class="panel">
                <div class="user-heading">
                    <h2>{{ $survey->title }}</h2>
                    <span class="muted">{{ $survey->status === 'published' ? __('Published') : __('Draft') }}</span>
                </div>
                @if ($survey->description)<p class="muted">{{ $survey->description }}</p>@endif
                @if (auth()->user()->isManager())
                    <p>{{ trans_choice('participants.count', $survey->responses_count, ['count' => $survey->responses_count]) }}</p>
                @elseif ($survey->own_responses_count)
                    <p class="notice">{{ __('You have already submitted this survey.') }}</p>
                @endif
                <a class="button button-small" href="{{ route('surveys.show', $survey) }}">{{ auth()->user()->isManager() ? __('View survey') : __('Open survey') }}</a>
            </article>
        @empty
            <section class="panel empty">{{ __('No surveys are currently available.') }}</section>
        @endforelse
    </div>
@endsection
