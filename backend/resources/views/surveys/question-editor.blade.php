<fieldset class="panel user-section" data-question>
    <legend>{{ __('Question') }}</legend>
    <label for="question-text-{{ $index }}">{{ __('Question text') }}</label>
    <input id="question-text-{{ $index }}" name="questions[{{ $index }}][text]" required maxlength="1000" value="{{ $question['text'] }}">
    <label for="question-type-{{ $index }}">{{ __('Question type') }}</label>
    <select id="question-type-{{ $index }}" name="questions[{{ $index }}][type]" data-type required>
        @foreach ([
            'short_text' => __('Short answer'),
            'long_text' => __('Long answer'),
            'single_choice' => __('Single choice'),
            'multiple_choice' => __('Multiple choice'),
            'dropdown' => __('Dropdown list'),
            'yes_no' => __('Yes / No'),
        ] as $type => $label)
            <option value="{{ $type }}" @selected($question['type'] === $type)>{{ $label }}</option>
        @endforeach
    </select>
    <label>
        <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked($question['is_required'])>
        {{ __('Required question') }}
    </label>
    @php($needsOptions = in_array($question['type'], ['single_choice', 'multiple_choice', 'dropdown'], true))
    <div data-options-wrap @if (!$needsOptions) hidden @endif>
        <label>{{ __('Options') }}</label>
        <div data-options data-name="questions[{{ $index }}][options][]">
            @forelse ($question['options'] as $option)
                <input name="questions[{{ $index }}][options][]" required maxlength="255" value="{{ $option }}" placeholder="{{ __('Option text') }}">
            @empty
                <input name="questions[{{ $index }}][options][]" required maxlength="255" placeholder="{{ __('Option text') }}" @disabled(!$needsOptions)>
                <input name="questions[{{ $index }}][options][]" required maxlength="255" placeholder="{{ __('Option text') }}" @disabled(!$needsOptions)>
            @endforelse
        </div>
        <button class="button-secondary button-small" type="button" data-add-option>{{ __('Add option') }}</button>
    </div>
    <button class="button-danger button-small" type="button" data-remove-question>{{ __('Remove question') }}</button>
</fieldset>
