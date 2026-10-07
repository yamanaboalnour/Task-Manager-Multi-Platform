<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyResponseService
{
    /**
     * @param  array<int, array{question_id: int, text?: ?string, option_ids?: array<int, int>}>  $answers
     */
    public function submit(Survey $survey, User $user, array $answers): SurveyResponse
    {
        return DB::transaction(function () use ($survey, $user, $answers): SurveyResponse {
            $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->getKey());
            if ($lockedSurvey->status !== Survey::STATUS_PUBLISHED || $user->isManager()) {
                abort(403);
            }

            if ($lockedSurvey->responses()->where('user_id', $user->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'survey' => [__('You have already submitted a response to this survey.')],
                ]);
            }

            $questions = $lockedSurvey->questions()->with('options')->get()->keyBy('id');
            $submitted = collect($answers)->keyBy(fn (array $answer) => (int) $answer['question_id']);

            foreach ($questions as $question) {
                $answer = $submitted->get($question->id);
                if ($question->is_required && ($answer === null || $this->isEmptyAnswer($question, $answer))) {
                    throw ValidationException::withMessages([
                        'answers' => [__('All required survey questions must be answered.')],
                    ]);
                }
            }

            if ($submitted->keys()->diff($questions->keys())->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'answers' => [__('One or more answers do not belong to this survey.')],
                ]);
            }

            try {
                $response = $lockedSurvey->responses()->create([
                    'user_id' => $user->getKey(),
                    'submitted_at' => now(),
                ]);

                foreach ($submitted as $questionId => $answer) {
                    $question = $questions->get($questionId);
                    if ($this->isEmptyAnswer($question, $answer)) {
                        continue;
                    }

                    $this->storeAnswer($response, $question, $answer);
                }

                return $response->load([
                    'user:id,first_name,last_name,name,email',
                    'answers.options',
                    'answers.question',
                ]);
            } catch (QueryException $exception) {
                $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
                if (in_array($sqlState, ['23000', '23505'], true)) {
                    throw ValidationException::withMessages([
                        'survey' => [__('You have already submitted a response to this survey.')],
                    ]);
                }

                throw $exception;
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function results(Survey $survey): array
    {
        $survey->load([
            'questions.options',
            'responses.user:id,first_name,last_name,name,email',
            'responses.answers.options',
        ]);

        $questions = $survey->questions->map(function (SurveyQuestion $question) use ($survey): array {
            $answers = $survey->responses
                ->flatMap(fn ($response) => $response->answers->where('question_id', $question->id)
                    ->map(fn ($answer) => [
                        'user' => $response->user,
                        'text' => $answer->answer_text,
                        'selected_options' => $answer->options->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                        ])->values(),
                    ]))
                ->values();

            $statistics = [];
            if (in_array($question->type, SurveyQuestion::OPTION_TYPES, true)) {
                $statistics = $question->options->map(fn ($option) => [
                    'option_id' => $option->id,
                    'label' => $option->label,
                    'count' => $answers->sum(
                        fn ($answer) => $answer['selected_options']->contains('id', $option->id) ? 1 : 0
                    ),
                ])->values()->all();
            } elseif ($question->type === SurveyQuestion::TYPE_YES_NO) {
                $statistics = [
                    ['answer' => 'yes', 'count' => $answers->where('text', 'yes')->count()],
                    ['answer' => 'no', 'count' => $answers->where('text', 'no')->count()],
                ];
            }

            return [
                'id' => $question->id,
                'text' => $question->text,
                'type' => $question->type,
                'is_required' => $question->is_required,
                'answer_count' => $answers->count(),
                'answers' => $answers,
                'statistics' => $statistics,
            ];
        })->values();

        return [
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'status' => $survey->status,
            ],
            'participant_count' => $survey->responses->count(),
            'questions' => $questions,
        ];
    }

    /**
     * @param  array<string, mixed>  $answer
     */
    private function isEmptyAnswer(SurveyQuestion $question, array $answer): bool
    {
        if (in_array($question->type, SurveyQuestion::OPTION_TYPES, true)) {
            return empty($answer['option_ids'] ?? []);
        }

        return trim((string) ($answer['text'] ?? '')) === '';
    }

    /**
     * @param  array<string, mixed>  $answer
     */
    private function storeAnswer(SurveyResponse $response, SurveyQuestion $question, array $answer): void
    {
        if ($question->type === SurveyQuestion::TYPE_YES_NO) {
            $response->answers()->create([
                'question_id' => $question->getKey(),
                'answer_text' => $answer['text'],
            ]);

            return;
        }

        if (in_array($question->type, SurveyQuestion::OPTION_TYPES, true)) {
            $optionIds = array_map('intval', $answer['option_ids'] ?? []);
            $selectedOptions = $question->options->whereIn('id', $optionIds);
            if (
                count($optionIds) !== $selectedOptions->count()
                || (in_array($question->type, [SurveyQuestion::TYPE_SINGLE_CHOICE, SurveyQuestion::TYPE_DROPDOWN], true)
                    && count($optionIds) !== 1)
            ) {
                throw ValidationException::withMessages([
                    'answers' => [__('Select valid options for the question.')],
                ]);
            }

            $surveyAnswer = $response->answers()->create([
                'question_id' => $question->getKey(),
                'answer_text' => null,
            ]);
            $surveyAnswer->options()->sync($optionIds);

            return;
        }

        $response->answers()->create([
            'question_id' => $question->getKey(),
            'answer_text' => trim((string) $answer['text']),
        ]);
    }
}
