<?php

namespace App\Http\Requests;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $survey = $this->route('survey');

        return ($this->user()?->isManager() ?? false)
            && (! $survey instanceof Survey || $survey->status === Survey::STATUS_DRAFT);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'questions' => ['sometimes', 'array', 'max:100'],
            'questions.*.text' => ['required', 'string', 'max:1000'],
            'questions.*.type' => ['required', Rule::in([
                SurveyQuestion::TYPE_SHORT_TEXT,
                SurveyQuestion::TYPE_LONG_TEXT,
                SurveyQuestion::TYPE_SINGLE_CHOICE,
                SurveyQuestion::TYPE_MULTIPLE_CHOICE,
                SurveyQuestion::TYPE_DROPDOWN,
                SurveyQuestion::TYPE_YES_NO,
            ])],
            'questions.*.is_required' => ['sometimes', 'boolean'],
            'questions.*.options' => ['sometimes', 'array', 'max:100'],
            'questions.*.options.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ($this->input('questions', []) as $index => $question) {
                    if (! is_array($question)) {
                        continue;
                    }

                    $type = $question['type'] ?? null;
                    $options = $question['options'] ?? [];
                    if (in_array($type, SurveyQuestion::OPTION_TYPES, true)) {
                        if (! is_array($options) || count($options) < 2) {
                            $validator->errors()->add(
                                "questions.{$index}.options",
                                __('Choice questions must have at least two options.')
                            );

                            continue;
                        }

                        $normalized = array_map(
                            static fn ($option) => mb_strtolower(trim((string) $option)),
                            $options
                        );
                        if (count(array_unique($normalized)) !== count($normalized)) {
                            $validator->errors()->add(
                                "questions.{$index}.options",
                                __('Question options must be unique.')
                            );
                        }
                    } elseif (! empty($options)) {
                        $validator->errors()->add(
                            "questions.{$index}.options",
                            __('This question type does not use options.')
                        );
                    }
                }
            },
        ];
    }
}
