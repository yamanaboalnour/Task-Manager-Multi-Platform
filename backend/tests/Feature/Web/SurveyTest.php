<?php

namespace Tests\Feature\Web;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_build_publish_and_review_worker_survey_responses(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();

        $payload = [
            'title' => 'استبيان الويب',
            'description' => 'اختبار واجهة الويب',
            'questions' => [
                [
                    'text' => 'ما رأيك؟',
                    'type' => SurveyQuestion::TYPE_SHORT_TEXT,
                    'is_required' => '1',
                ],
                [
                    'text' => 'هل استفدت؟',
                    'type' => SurveyQuestion::TYPE_YES_NO,
                    'is_required' => '1',
                ],
            ],
        ];

        $this->actingAs($manager)->get('/surveys/create')
            ->assertOk()
            ->assertSee('إنشاء استبيان');
        $this->actingAs($manager)->post('/surveys', $payload)
            ->assertRedirect();
        $survey = Survey::query()->where('title', 'استبيان الويب')->firstOrFail();

        $this->actingAs($manager)
            ->post("/surveys/{$survey->id}/publish")
            ->assertRedirect(route('surveys.show', $survey));

        $this->actingAs($worker)
            ->get('/surveys')
            ->assertOk()
            ->assertSee('استبيان الويب');
        $this->actingAs($worker)
            ->get(route('surveys.show', $survey))
            ->assertOk()
            ->assertSee('ما رأيك؟');

        $questions = $survey->questions()->orderBy('position')->get();
        $this->actingAs($worker)
            ->post(route('surveys.responses.store', $survey), [
                'answers' => [
                    $questions[0]->id => [
                        'question_id' => $questions[0]->id,
                        'text' => 'مفيد',
                    ],
                    $questions[1]->id => [
                        'question_id' => $questions[1]->id,
                        'text' => 'yes',
                    ],
                ],
            ])
            ->assertRedirect(route('surveys.show', $survey));

        $this->actingAs($worker)
            ->post(route('surveys.responses.store', $survey), [
                'answers' => [
                    $questions[0]->id => [
                        'question_id' => $questions[0]->id,
                        'text' => 'إعادة',
                    ],
                    $questions[1]->id => [
                        'question_id' => $questions[1]->id,
                        'text' => 'no',
                    ],
                ],
            ])
            ->assertSessionHasErrors('survey');

        $this->actingAs($manager)
            ->get(route('surveys.responses.index', $survey))
            ->assertOk()
            ->assertSee('نتائج الاستبيان')
            ->assertSee('مفيد')
            ->assertSee($worker->name);
    }

    public function test_worker_cannot_open_drafts_or_manager_survey_tools(): void
    {
        $manager = User::factory()->manager()->create();
        $worker = User::factory()->create();
        $draft = Survey::query()->create([
            'manager_id' => $manager->id,
            'title' => 'مسودة',
            'status' => Survey::STATUS_DRAFT,
        ]);

        $this->actingAs($worker)->get('/surveys/create')->assertForbidden();
        $this->actingAs($worker)->get(route('surveys.show', $draft))->assertForbidden();
        $this->actingAs($worker)->get(route('surveys.responses.index', $draft))->assertForbidden();
    }
}
