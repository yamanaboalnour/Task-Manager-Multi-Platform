<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSurveyRequest;
use App\Models\Survey;
use App\Services\SurveyManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Survey::class);
        $user = $request->user();

        $surveys = Survey::query()
            ->with('questions.options')
            ->withCount(['responses as own_responses_count' => fn ($query) => $query->where('user_id', $user->getKey())])
            ->when(! $user->isManager(), fn ($query) => $query->where('status', Survey::STATUS_PUBLISHED))
            ->orderByDesc('updated_at')
            ->get()
            ->each(fn (Survey $survey) => $survey->setAttribute(
                'has_responded',
                $survey->own_responses_count > 0
            ));

        return response()->json($surveys);
    }

    public function show(Request $request, Survey $survey)
    {
        Gate::authorize('view', $survey);
        $survey->load('questions.options');
        $survey->setAttribute(
            'has_responded',
            $survey->responses()->where('user_id', $request->user()->getKey())->exists()
        );

        return response()->json($survey);
    }

    public function store(SaveSurveyRequest $request, SurveyManagementService $surveys)
    {
        Gate::authorize('create', Survey::class);

        return response()->json(
            $surveys->create($request->user(), $request->validated()),
            201
        );
    }

    public function update(
        SaveSurveyRequest $request,
        Survey $survey,
        SurveyManagementService $surveys,
    ) {
        Gate::authorize('update', $survey);

        return response()->json($surveys->update($survey, $request->validated()));
    }

    public function publish(Survey $survey, SurveyManagementService $surveys)
    {
        Gate::authorize('publish', $survey);

        return response()->json($surveys->publish($survey));
    }

    public function destroy(Survey $survey, SurveyManagementService $surveys)
    {
        Gate::authorize('delete', $survey);
        $surveys->delete($survey);

        return response()->noContent();
    }
}
