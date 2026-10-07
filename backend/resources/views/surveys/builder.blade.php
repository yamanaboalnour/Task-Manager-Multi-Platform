@extends('layouts.app')

@section('title', $survey ? __('Edit survey') : __('Create a survey'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $survey ? __('Edit survey') : __('Create a survey') }}</h1>
            <p class="muted">{{ __('Choose question types, options, order, and required fields.') }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="errors" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $existingQuestions = old('questions', $survey?->questions->map(fn ($question) => [
            'text' => $question->text,
            'type' => $question->type,
            'is_required' => $question->is_required,
            'options' => $question->options->pluck('label')->all(),
        ])->all() ?? []);
    @endphp
    <form method="POST" action="{{ $survey ? route('surveys.update', $survey) : route('surveys.store') }}" class="panel">
        @csrf
        @if ($survey) @method('PUT') @endif
        <label for="survey-title">{{ __('Survey title') }}</label>
        <input id="survey-title" name="title" required maxlength="255" value="{{ old('title', $survey?->title) }}">
        <label for="survey-description">{{ __('Survey description') }}</label>
        <textarea id="survey-description" name="description">{{ old('description', $survey?->description) }}</textarea>

        <div id="question-list">
            @foreach ($existingQuestions as $index => $question)
                @include('surveys.question-editor', ['index' => $index, 'question' => $question])
            @endforeach
        </div>
        <button class="button-secondary" type="button" id="add-question">{{ __('Add question') }}</button>
        <button class="full" type="submit">{{ __('Save draft') }}</button>
    </form>

    <template id="question-template">
        @include('surveys.question-editor', ['index' => '__INDEX__', 'question' => ['text' => '', 'type' => 'short_text', 'is_required' => false, 'options' => []]])
    </template>
    <script>
        (() => {
            const list = document.getElementById('question-list');
            let nextIndex = {{ count($existingQuestions) }};
            const addQuestion = () => {
                const template = document.getElementById('question-template').innerHTML
                    .replaceAll('__INDEX__', String(nextIndex++));
                list.insertAdjacentHTML('beforeend', template);
            };
            document.getElementById('add-question').addEventListener('click', addQuestion);
            list.addEventListener('click', event => {
                if (event.target.matches('[data-remove-question]')) {
                    event.target.closest('[data-question]').remove();
                }
                if (event.target.matches('[data-add-option]')) {
                    const field = event.target.closest('[data-question]').querySelector('[data-options]');
                    const input = document.createElement('input');
                    input.name = field.dataset.name;
                    input.required = true;
                    input.maxLength = 255;
                    input.placeholder = @json(__('Option text'));
                    field.append(input);
                }
            });
            list.addEventListener('change', event => {
                if (event.target.matches('[data-type]')) {
                    const options = event.target.closest('[data-question]').querySelector('[data-options-wrap]');
                    options.hidden = !['single_choice', 'multiple_choice', 'dropdown'].includes(event.target.value);
                    options.querySelectorAll('input').forEach(input => input.disabled = options.hidden);
                }
            });
            list.querySelectorAll('[data-options-wrap]').forEach(options => {
                options.querySelectorAll('input').forEach(input => input.disabled = options.hidden);
            });
        })();
    </script>
@endsection
