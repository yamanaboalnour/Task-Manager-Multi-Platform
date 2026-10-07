@extends('layouts.app')

@section('title', __('Survey results'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ __('Results: :title', ['title' => $survey['title']]) }}</h1>
            <p class="muted">{{ trans_choice('participants.count', $participant_count, ['count' => $participant_count]) }}</p>
        </div>
    </div>

    @foreach ($questions as $question)
        <section class="panel user-section">
            <h2>{{ $question['text'] }}</h2>
            <p class="muted">{{ trans_choice('answers.count', $question['answer_count'], ['count' => $question['answer_count']]) }}</p>
            @if ($question['statistics'])
                <ul>
                    @foreach ($question['statistics'] as $statistic)
                        <li>{{ $statistic['label'] ?? ($statistic['answer'] === 'yes' ? __('Yes') : __('No')) }}: {{ $statistic['count'] }}</li>
                    @endforeach
                </ul>
            @endif
            <div class="user-grid">
                @foreach ($question['answers'] as $answer)
                    <article class="task-card">
                        <strong>{{ $answer['user']->name }}</strong>
                        <p>{{ $answer['text'] ?? $answer['selected_options']->pluck('label')->join('، ') }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
