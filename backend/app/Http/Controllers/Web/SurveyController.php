<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSurveyRequest;
use App\Http\Requests\SubmitSurveyResponseRequest;
use App\Models\Survey;
use App\Services\SurveyManagementService;
use App\Services\SurveyResponseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Survey::class);
        $user = $request->user();

        return view('surveys.index', [
            'surveys' => Survey::query()
                ->withCount([
                    'responses as own_responses_count' => fn ($query) => $query->where('user_id', $user->getKey()),
                    'responses',
                ])
                ->when(! $user->isManager(), fn ($query) => $query->where('status', Survey::STATUS_PUBLISHED))
                ->orderByDesc('updated_at')
                ->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Survey::class);

        return view('surveys.builder', ['survey' => null]);
    }

    public function store(SaveSurveyRequest $request, SurveyManagementService $surveys): RedirectResponse
    {
        Gate::authorize('create', Survey::class);
        $survey = $surveys->create($request->user(), $request->validated());

        return redirect()->route('surveys.show', $survey)
            ->with('status', __('Survey saved as draft.'));
    }

    public function show(Request $request, Survey $survey): View
    {
        Gate::authorize('view', $survey);
        $survey->load('questions.options');
        $hasResponded = $survey->responses()
            ->where('user_id', $request->user()->getKey())
            ->exists();

        return view('surveys.show', [
            'survey' => $survey,
            'hasResponded' => $hasResponded,
        ]);
    }

    public function edit(Survey $survey): View
    {
        Gate::authorize('update', $survey);

        return view('surveys.builder', [
            'survey' => $survey->load('questions.options'),
        ]);
    }

    public function update(
        SaveSurveyRequest $request,
        Survey $survey,
        SurveyManagementService $surveys,
    ): RedirectResponse {
        Gate::authorize('update', $survey);
        $surveys->update($survey, $request->validated());

        return redirect()->route('surveys.show', $survey)
            ->with('status', __('Survey updated successfully.'));
    }

    public function publish(Survey $survey, SurveyManagementService $surveys): RedirectResponse
    {
        Gate::authorize('publish', $survey);
        $surveys->publish($survey);

        return redirect()->route('surveys.show', $survey)
            ->with('status', __('Survey published successfully.'));
    }

    public function destroy(Survey $survey, SurveyManagementService $surveys): RedirectResponse
    {
        Gate::authorize('delete', $survey);
        $surveys->delete($survey);

        return redirect()->route('surveys.index')
            ->with('status', __('Survey deleted successfully.'));
    }

    public function respond(
        SubmitSurveyResponseRequest $request,
        Survey $survey,
        SurveyResponseService $responses,
    ): RedirectResponse {
        $responses->submit($survey, $request->user(), $request->validated()['answers']);

        return redirect()->route('surveys.show', $survey)
            ->with('status', __('Survey response submitted successfully.'));
    }

    public function results(Survey $survey, SurveyResponseService $responses): View
    {
        Gate::authorize('viewResponses', $survey);

        return view('surveys.results', $responses->results($survey));
    }
}
