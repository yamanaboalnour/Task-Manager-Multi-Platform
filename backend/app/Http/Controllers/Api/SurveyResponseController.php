<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSurveyResponseRequest;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Services\SurveyResponseService;
use Illuminate\Support\Facades\Gate;

class SurveyResponseController extends Controller
{
    public function store(
        SubmitSurveyResponseRequest $request,
        Survey $survey,
        SurveyResponseService $responses,
    ) {
        Gate::authorize('respond', $survey);
        $response = $responses->submit($survey, $request->user(), $request->validated()['answers']);

        return response()->json([
            'message' => __('Survey response submitted successfully.'),
            'response' => $response,
        ], 201);
    }

    public function index(Survey $survey, SurveyResponseService $responses)
    {
        Gate::authorize('viewResponses', $survey);

        return response()->json($responses->results($survey));
    }

    public function mine()
    {
        return response()->json(
            SurveyResponse::query()
                ->where('user_id', request()->user()->getKey())
                ->with('survey:id,title,status')
                ->orderByDesc('submitted_at')
                ->get()
        );
    }
}
