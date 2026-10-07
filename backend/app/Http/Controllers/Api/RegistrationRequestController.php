<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\RegistrationRequest;
use App\Services\RegistrationRequestService;
use Illuminate\Support\Facades\Gate;

class RegistrationRequestController extends Controller
{
    public function store(
        StoreRegistrationRequest $request,
        RegistrationRequestService $requests,
    ) {
        return response()->json([
            'message' => __('Your account request has been submitted for review.'),
            'registration_request' => $requests->submit($request->validated()),
        ], 201);
    }

    public function index()
    {
        Gate::authorize('viewAny', RegistrationRequest::class);

        return response()->json(
            RegistrationRequest::query()
                ->with(['user:id,first_name,last_name,name,email,role', 'reviewer:id,name'])
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function approve(
        RegistrationRequest $registrationRequest,
        RegistrationRequestService $requests,
    ) {
        Gate::authorize('approve', $registrationRequest);

        return response()->json([
            'message' => __('Registration request approved.'),
            'registration_request' => $requests->approve($registrationRequest, request()->user()),
        ]);
    }

    public function reject(
        RegistrationRequest $registrationRequest,
        RegistrationRequestService $requests,
    ) {
        Gate::authorize('reject', $registrationRequest);

        return response()->json([
            'message' => __('Registration request rejected.'),
            'registration_request' => $requests->reject($registrationRequest, request()->user()),
        ]);
    }
}
