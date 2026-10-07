@extends('layouts.app')

@section('title', __('Registration requests'))

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ __('Registration requests') }}</h1>
            <p class="muted">{{ trans_choice('registration-requests.count', $pendingCount, ['count' => $pendingCount]) }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="errors" role="alert">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @forelse ($requests as $registrationRequest)
        <section class="panel user-section">
            <div class="user-heading">
                <h2>{{ $registrationRequest->first_name }} {{ $registrationRequest->last_name }}</h2>
                <span class="muted">
                    @switch($registrationRequest->status)
                        @case('pending') {{ __('Pending') }} @break
                        @case('approved') {{ __('Approved') }} @break
                        @case('rejected') {{ __('Rejected') }} @break
                    @endswitch
                </span>
            </div>
            <p><span dir="ltr">{{ $registrationRequest->email }}</span></p>
            <p class="muted">{{ __('Requested at :date', ['date' => $registrationRequest->created_at->format('Y-m-d H:i')]) }}</p>
            @if ($registrationRequest->status === \App\Models\RegistrationRequest::STATUS_PENDING)
                <div class="task-actions">
                    <form method="POST" action="{{ route('registration-requests.approve', $registrationRequest) }}">
                        @csrf
                        <button type="submit">{{ __('Approve') }}</button>
                    </form>
                    <form method="POST" action="{{ route('registration-requests.reject', $registrationRequest) }}">
                        @csrf
                        <button class="button-danger" type="submit">{{ __('Reject') }}</button>
                    </form>
                </div>
            @endif
        </section>
    @empty
        <section class="panel empty">{{ __('No registration requests yet.') }}</section>
    @endforelse
@endsection
