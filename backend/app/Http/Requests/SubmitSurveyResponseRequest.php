<?php

namespace App\Http\Requests;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitSurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $survey = $this->route('survey');

        return [
            'answers' => ['required', 'array', 'max:100'],
            'answers.*.question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('survey_questions', 'id')->where('survey_id', $survey?->getKey()),
            ],
            'answers.*.text' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'answers.*.option_ids' => ['sometimes', 'array', 'max:100'],
            'answers.*.option_ids.*' => [
                'integer',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $parts = explode('.', $attribute);
                    $answerIndex = $parts[1] ?? null;
                    $questionId = $answerIndex === null
                        ? null
                        : $this->input("answers.{$answerIndex}.question_id");

                    if (! $questionId || ! SurveyQuestion::query()
                        ->whereKey($questionId)
                        ->whereHas('options', fn ($query) => $query->whereKey($value))
                        ->exists()) {
                        $fail(__('Select valid options for the question.'));
                    }
                },
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $survey = $this->route('survey');
                if (! $survey instanceof Survey) {
                    return;
                }

                $questions = $survey->questions()->with('options')->get()->keyBy('id');
                foreach ($this->input('answers', []) as $answer) {
                    if (! is_array($answer) || ! isset($answer['question_id'])) {
                        continue;
                    }

                    $question = $questions->get((int) $answer['question_id']);
                    if (! $question) {
                        continue;
                    }

                    $optionIds = array_map('intval', $answer['option_ids'] ?? []);
                    $selected = $question->options->whereIn('id', $optionIds);
                    if (
                        count($optionIds) !== $selected->count()
                        || (in_array($question->type, [SurveyQuestion::TYPE_SINGLE_CHOICE, SurveyQuestion::TYPE_DROPDOWN], true)
                            && count($optionIds) > 1)
                        || (! in_array($question->type, SurveyQuestion::OPTION_TYPES, true) && $optionIds !== [])
                    ) {
                        $validator->errors()->add(
                            'answers',
                            __('Select valid options for the question.')
                        );
                    }

                    if (
                        $question->is_required
                        && (
                            (in_array($question->type, SurveyQuestion::OPTION_TYPES, true) && $optionIds === [])
                            || (in_array($question->type, [SurveyQuestion::TYPE_SHORT_TEXT, SurveyQuestion::TYPE_LONG_TEXT, SurveyQuestion::TYPE_YES_NO], true)
                                && trim((string) ($answer['text'] ?? '')) === '')
                        )
                    ) {
                        $validator->errors()->add(
                            "answers.{$answer['question_id']}",
                            __('This question is required.')
                        );
                    }

                    if (
                        $question->type === SurveyQuestion::TYPE_YES_NO
                        && isset($answer['text'])
                        && ! in_array($answer['text'], ['yes', 'no', ''], true)
                    ) {
                        $validator->errors()->add(
                            'answers',
                            __('Choose yes or no for the yes/no question.')
                        );
                    }
                }
            },
        ];
    }
}
