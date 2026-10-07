<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyManagementService
{
    /**
     * @param  array{title: string, description?: ?string, questions: array<int, array<string, mixed>>}  $attributes
     */
    public function create(User $manager, array $attributes): Survey
    {
        return DB::transaction(function () use ($manager, $attributes): Survey {
            $survey = Survey::query()->create([
                'manager_id' => $manager->getKey(),
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'status' => Survey::STATUS_DRAFT,
            ]);

            $this->replaceQuestions($survey, $attributes['questions'] ?? []);

            return $survey->load('questions.options');
        });
    }

    /**
     * @param  array{title: string, description?: ?string, questions: array<int, array<string, mixed>>}  $attributes
     */
    public function update(Survey $survey, array $attributes): Survey
    {
        return DB::transaction(function () use ($survey, $attributes): Survey {
            $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->getKey());
            $this->ensureUnanswered($lockedSurvey);
            $lockedSurvey->update([
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
            ]);
            $this->replaceQuestions($lockedSurvey, $attributes['questions'] ?? []);

            return $lockedSurvey->refresh()->load('questions.options');
        });
    }

    public function publish(Survey $survey): Survey
    {
        return DB::transaction(function () use ($survey): Survey {
            $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->getKey());

            if (! $lockedSurvey->questions()->exists()) {
                throw ValidationException::withMessages([
                    'questions' => [__('A survey must contain at least one question before publishing.')],
                ]);
            }

            if ($lockedSurvey->status !== Survey::STATUS_PUBLISHED) {
                $lockedSurvey->update([
                    'status' => Survey::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]);
            }

            return $lockedSurvey->refresh()->load('questions.options');
        });
    }

    public function delete(Survey $survey): void
    {
        DB::transaction(function () use ($survey): void {
            $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->getKey());
            $this->ensureUnanswered($lockedSurvey);
            $lockedSurvey->delete();
        });
    }

    private function ensureUnanswered(Survey $survey): void
    {
        if ($survey->responses()->exists()) {
            throw ValidationException::withMessages([
                'survey' => [__('A survey with submitted responses cannot be changed or deleted.')],
            ]);
        }

        if ($survey->status === Survey::STATUS_PUBLISHED) {
            throw ValidationException::withMessages([
                'survey' => [__('A published survey cannot be changed or deleted.')],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function replaceQuestions(Survey $survey, array $questions): void
    {
        $survey->questions()->delete();

        foreach (array_values($questions) as $position => $attributes) {
            $question = $survey->questions()->create([
                'text' => trim($attributes['text']),
                'type' => $attributes['type'],
                'is_required' => (bool) $attributes['is_required'],
                'position' => $position,
            ]);

            foreach (array_values($attributes['options'] ?? []) as $optionPosition => $label) {
                $question->options()->create([
                    'label' => trim($label),
                    'position' => $optionPosition,
                ]);
            }
        }
    }
}
