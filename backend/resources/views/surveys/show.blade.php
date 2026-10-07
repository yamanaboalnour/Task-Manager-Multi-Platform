@extends('layouts.app')

@section('title', $survey->title)

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $survey->title }}</h1>
            @if ($survey->description)<p class="muted">{{ $survey->description }}</p>@endif
        </div>
    </div>

    @if ($errors->any())
        <div class="errors" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (auth()->user()->isManager())
        <section class="panel">
            <p>{{ $survey->status === 'published' ? __('Published') : __('Draft') }}</p>
            <p>{{ trans_choice('participants.count', $survey->responses()->count(), ['count' => $survey->responses()->count()]) }}</p>
            <div class="task-actions">
                <a class="button button-secondary" href="{{ route('surveys.edit', $survey) }}">{{ __('Edit survey') }}</a>
                <a class="button button-secondary" href="{{ route('surveys.responses.index', $survey) }}">{{ __('View results') }}</a>
                @if ($survey->status === 'draft')
                    <form method="POST" action="{{ route('surveys.publish', $survey) }}">
                        @csrf
                        <button type="submit">{{ __('Publish survey') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('surveys.destroy', $survey) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button-danger" type="submit">{{ __('Delete survey') }}</button>
                </form>
            </div>
        </section>
    @elseif ($hasResponded)
        <section class="panel notice">{{ __('You have already submitted this survey.') }}</section>
    @else
        <form method="POST" action="{{ route('surveys.responses.store', $survey) }}" class="panel">
            @csrf
            @foreach ($survey->questions as $question)
                <fieldset class="user-section">
                    <legend>{{ $question->text }} @if ($question->is_required)<span aria-label="{{ __('Required') }}">*</span>@endif</legend>
                    @switch($question->type)
                        @case('short_text')
                            <input name="answers[{{ $question->id }}][question_id]" type="hidden" value="{{ $question->id }}">
                            <input name="answers[{{ $question->id }}][text]" @required($question->is_required) maxlength="10000">
                            @break
                        @case('long_text')
                            <input name="answers[{{ $question->id }}][question_id]" type="hidden" value="{{ $question->id }}">
                            <textarea name="answers[{{ $question->id }}][text]" @required($question->is_required) maxlength="10000"></textarea>
                            @break
                        @case('yes_no')
                            <input name="answers[{{ $question->id }}][question_id]" type="hidden" value="{{ $question->id }}">
                            <select name="answers[{{ $question->id }}][text]" @required($question->is_required)>
                                <option value="">{{ __('Choose an answer') }}</option>
                                <option value="yes">{{ __('Yes') }}</option>
                                <option value="no">{{ __('No') }}</option>
                            </select>
                            @break
                        @default
                            @if ($question->type === 'dropdown')
                                <select name="answers[{{ $question->id }}][option_ids][]" @required($question->is_required)>
                                    <option value="">{{ __('Choose an answer') }}</option>
                                    @foreach ($question->options as $option)
                                        <option value="{{ $option->id }}">{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            @else
                                @foreach ($question->options as $option)
                                    <label>
                                        <input type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}" name="answers[{{ $question->id }}][option_ids]{{ $question->type === 'multiple_choice' ? '[]' : '' }}" value="{{ $option->id }}" @required($question->is_required && $question->type === 'single_choice' && $loop->first)>
                                        {{ $option->label }}
                                    </label>
                                @endforeach
                            @endif
                            <input name="answers[{{ $question->id }}][question_id]" type="hidden" value="{{ $question->id }}">
                    @endswitch
                </fieldset>
            @endforeach
            <button class="full" type="submit">{{ __('Submit response') }}</button>
        </form>
    @endif
@endsection
