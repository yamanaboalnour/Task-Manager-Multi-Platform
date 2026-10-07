<?php

namespace Tests\Feature\Api;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    public function withToken(#[\SensitiveParameter] string $token, string $type = 'Bearer'): static
    {
        Auth::forgetGuards();

        return parent::withToken($token, $type);
    }

    public function test_manager_can_create_and_publish_a_survey_for_workers(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $managerToken = $manager->createToken('manager')->plainTextToken;
        $workerToken = $worker->createToken('worker')->plainTextToken;
        $payload = $this->surveyPayload();

        $created = $this->withToken($managerToken)
            ->postJson('/api/v1/surveys', $payload)
            ->assertCreated()
            ->assertJsonPath('status', Survey::STATUS_DRAFT)
            ->assertJsonPath('questions.0.position', 0)
            ->assertJsonPath('questions.1.options.1.label', 'أخرى');
        $surveyId = $created->json('id');

        $this->withToken($workerToken)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_WORKER);

        $this->withToken($workerToken)
            ->getJson('/api/v1/surveys')
            ->assertOk()
            ->assertJsonCount(0);
        $this->withToken($workerToken)
            ->getJson("/api/v1/surveys/{$surveyId}")
            ->assertForbidden();

        $this->withToken($managerToken)
            ->postJson("/api/v1/surveys/{$surveyId}/publish")
            ->assertOk()
            ->assertJsonPath('status', Survey::STATUS_PUBLISHED);

        $this->withToken($workerToken)
            ->getJson('/api/v1/surveys')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', $payload['title'])
            ->assertJsonPath('0.has_responded', false);
    }

    public function test_workers_cannot_create_publish_or_read_survey_results(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $draft = Survey::query()->create([
            'manager_id' => $manager->id,
            'title' => 'مسودة',
            'status' => Survey::STATUS_DRAFT,
        ]);
        $workerToken = $worker->createToken('worker')->plainTextToken;

        $this->withToken($workerToken)
            ->postJson('/api/v1/surveys', $this->surveyPayload())
            ->assertForbidden();
        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$draft->id}/publish")
            ->assertForbidden();
        $this->withToken($workerToken)
            ->getJson("/api/v1/surveys/{$draft->id}/responses")
            ->assertForbidden();
    }

    public function test_worker_can_submit_once_and_manager_can_review_answers_and_statistics(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create([
            'first_name' => 'عامل',
            'last_name' => 'تجريبي',
            'name' => 'عامل تجريبي',
        ]);
        $managerToken = $manager->createToken('manager')->plainTextToken;
        $workerToken = $worker->createToken('worker')->plainTextToken;

        $survey = $this->withToken($managerToken)
            ->postJson('/api/v1/surveys', $this->surveyPayload())
            ->assertCreated()
            ->json();
        $this->withToken($managerToken)
            ->postJson("/api/v1/surveys/{$survey['id']}/publish")
            ->assertOk();

        $survey = Survey::query()->with('questions.options')->findOrFail($survey['id']);
        $choiceQuestion = $survey->questions->firstWhere('type', SurveyQuestion::TYPE_SINGLE_CHOICE);
        $optionId = $choiceQuestion->options->first()->id;
        $answers = [
            ['question_id' => $survey->questions[0]->id, 'text' => 'إجابة مفيدة'],
            ['question_id' => $choiceQuestion->id, 'option_ids' => [$optionId]],
            ['question_id' => $survey->questions[2]->id, 'text' => 'yes'],
        ];

        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$survey->id}/responses", ['answers' => $answers])
            ->assertCreated()
            ->assertJsonPath('message', 'تم إرسال إجابة الاستبيان بنجاح.');

        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$survey->id}/responses", ['answers' => $answers])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('survey');
        $this->withToken($workerToken)
            ->getJson("/api/v1/surveys/{$survey->id}/responses")
            ->assertForbidden();

        $results = $this->withToken($managerToken)
            ->getJson("/api/v1/surveys/{$survey->id}/responses")
            ->assertOk()
            ->assertJsonPath('participant_count', 1)
            ->assertJsonPath('questions.0.answer_count', 1)
            ->assertJsonPath('questions.0.answers.0.text', 'إجابة مفيدة')
            ->assertJsonPath('questions.1.statistics.0.count', 1)
            ->assertJsonPath('questions.2.statistics.0.count', 1);

        $this->assertSame($worker->id, $results->json('questions.0.answers.0.user.id'));
        $this->withToken($workerToken)
            ->getJson("/api/v1/surveys/{$survey->id}")
            ->assertJsonPath('has_responded', true);
    }

    public function test_required_questions_and_question_owned_options_are_validated(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $managerToken = $manager->createToken('manager')->plainTextToken;
        $workerToken = $worker->createToken('worker')->plainTextToken;
        $surveyId = $this->withToken($managerToken)
            ->postJson('/api/v1/surveys', $this->surveyPayload())
            ->assertCreated()
            ->json('id');
        $this->withToken($managerToken)->postJson("/api/v1/surveys/{$surveyId}/publish")->assertOk();
        $survey = Survey::query()->with('questions.options')->findOrFail($surveyId);
        $question = $survey->questions->firstWhere('type', SurveyQuestion::TYPE_SINGLE_CHOICE);
        $foreignOption = 999999;

        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$surveyId}/responses", ['answers' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answers');

        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$surveyId}/responses", [
                'answers' => [
                    ['question_id' => $survey->questions[0]->id, 'text' => 'ok'],
                    ['question_id' => $question->id, 'option_ids' => [$foreignOption]],
                    ['question_id' => $survey->questions[2]->id, 'text' => 'no'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('answers');

        $this->assertDatabaseCount('survey_responses', 0);
    }

    public function test_survey_questions_cannot_be_changed_or_deleted_after_responses(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $managerToken = $manager->createToken('manager')->plainTextToken;
        $workerToken = $worker->createToken('worker')->plainTextToken;
        $surveyId = $this->withToken($managerToken)
            ->postJson('/api/v1/surveys', $this->surveyPayload())
            ->assertCreated()
            ->json('id');
        $this->withToken($managerToken)->postJson("/api/v1/surveys/{$surveyId}/publish")->assertOk();
        $survey = Survey::query()->with('questions.options')->findOrFail($surveyId);
        $answers = [
            ['question_id' => $survey->questions[0]->id, 'text' => 'done'],
            ['question_id' => $survey->questions[1]->id, 'option_ids' => [$survey->questions[1]->options[0]->id]],
            ['question_id' => $survey->questions[2]->id, 'text' => 'no'],
        ];
        $this->withToken($workerToken)
            ->postJson("/api/v1/surveys/{$surveyId}/responses", ['answers' => $answers])
            ->assertCreated();

        $this->withToken($managerToken)
            ->putJson("/api/v1/surveys/{$surveyId}", $this->surveyPayload())
            ->assertForbidden();
        $this->withToken($managerToken)
            ->deleteJson("/api/v1/surveys/{$surveyId}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('survey');
    }

    public function test_survey_builder_validates_choice_options_and_allows_empty_drafts(): void
    {
        $manager = User::factory()->manager()->create();
        $token = $manager->createToken('manager')->plainTextToken;
        $payload = $this->surveyPayload();
        $payload['questions'][1]['options'] = ['خيار واحد'];

        $this->withToken($token)
            ->postJson('/api/v1/surveys', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('questions.1.options');

        $emptyDraft = $this->withToken($token)
            ->postJson('/api/v1/surveys', [
                'title' => 'فارغ',
                'questions' => [],
            ])
            ->assertCreated()
            ->assertJsonCount(0, 'questions');

        $this->withToken($token)
            ->postJson("/api/v1/surveys/{$emptyDraft->json('id')}/publish")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('questions');
    }

    /**
     * @return array<string, mixed>
     */
    private function surveyPayload(): array
    {
        return [
            'title' => 'استبيان تجريبي',
            'description' => 'وصف الاختبار',
            'questions' => [
                [
                    'text' => 'ما رأيك؟',
                    'type' => SurveyQuestion::TYPE_SHORT_TEXT,
                    'is_required' => true,
                ],
                [
                    'text' => 'القسم',
                    'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
                    'is_required' => true,
                    'options' => ['الإدارة', 'أخرى'],
                ],
                [
                    'text' => 'هل استفدت؟',
                    'type' => SurveyQuestion::TYPE_YES_NO,
                    'is_required' => true,
                ],
            ],
        ];
    }
}
