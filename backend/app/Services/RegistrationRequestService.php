<?php

namespace App\Services;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationRequestService
{
    /**
     * @param  array{first_name: string, last_name: string, email: string, password: string}  $attributes
     */
    public function submit(array $attributes): RegistrationRequest
    {
        $email = mb_strtolower(trim($attributes['email']));

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('This email address is already registered.')],
            ]);
        }

        if (RegistrationRequest::query()->where('email', $email)->where('status', RegistrationRequest::STATUS_PENDING)->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('A registration request for this email is already pending.')],
            ]);
        }

        try {
            return RegistrationRequest::query()->create([
                'first_name' => trim($attributes['first_name']),
                'last_name' => trim($attributes['last_name']),
                'email' => $email,
                'password_hash' => Hash::make($attributes['password']),
                'status' => RegistrationRequest::STATUS_PENDING,
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                throw ValidationException::withMessages([
                    'email' => [__('A registration request for this email is already pending.')],
                ]);
            }

            throw $exception;
        }
    }

    public function approve(RegistrationRequest $registrationRequest, User $manager): RegistrationRequest
    {
        return DB::transaction(function () use ($registrationRequest, $manager): RegistrationRequest {
            $request = RegistrationRequest::query()
                ->lockForUpdate()
                ->findOrFail($registrationRequest->getKey());

            $this->ensurePending($request);

            if (User::query()->where('email', $request->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => [__('This email address is already registered.')],
                ]);
            }

            $name = trim($request->first_name.' '.$request->last_name);
            $user = User::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $name,
                'email' => $request->email,
                'password' => $request->password_hash,
                'role' => User::ROLE_WORKER,
            ]);

            $request->update([
                'status' => RegistrationRequest::STATUS_APPROVED,
                'reviewed_by' => $manager->getKey(),
                'reviewed_at' => now(),
                'user_id' => $user->getKey(),
                'password_hash' => null,
            ]);

            return $request->refresh()->load(['user:id,first_name,last_name,name,email,role', 'reviewer:id,name']);
        });
    }

    public function reject(RegistrationRequest $registrationRequest, User $manager): RegistrationRequest
    {
        return DB::transaction(function () use ($registrationRequest, $manager): RegistrationRequest {
            $request = RegistrationRequest::query()
                ->lockForUpdate()
                ->findOrFail($registrationRequest->getKey());

            $this->ensurePending($request);
            $request->update([
                'status' => RegistrationRequest::STATUS_REJECTED,
                'reviewed_by' => $manager->getKey(),
                'reviewed_at' => now(),
                'password_hash' => null,
            ]);

            return $request->refresh()->load('reviewer:id,name');
        });
    }

    private function ensurePending(RegistrationRequest $registrationRequest): void
    {
        if ($registrationRequest->status !== RegistrationRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'request' => [__('This registration request has already been reviewed.')],
            ]);
        }
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
