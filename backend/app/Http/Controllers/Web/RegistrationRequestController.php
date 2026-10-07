<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Services\RegistrationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', RegistrationRequest::class);

        return view('registration-requests.index', [
            'requests' => RegistrationRequest::query()
                ->with(['user:id,name,email', 'reviewer:id,name'])
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at')
                ->get(),
            'pendingCount' => RegistrationRequest::query()
                ->where('status', RegistrationRequest::STATUS_PENDING)
                ->count(),
        ]);
    }

    public function approve(
        RegistrationRequest $registrationRequest,
        RegistrationRequestService $requests,
    ): RedirectResponse {
        Gate::authorize('approve', $registrationRequest);
        $requests->approve($registrationRequest, request()->user());

        return redirect()->route('registration-requests.index')
            ->with('status', __('Registration request approved.'));
    }

    public function reject(
        RegistrationRequest $registrationRequest,
        RegistrationRequestService $requests,
    ): RedirectResponse {
        Gate::authorize('reject', $registrationRequest);
        $requests->reject($registrationRequest, request()->user());

        return redirect()->route('registration-requests.index')
            ->with('status', __('Registration request rejected.'));
    }
}
