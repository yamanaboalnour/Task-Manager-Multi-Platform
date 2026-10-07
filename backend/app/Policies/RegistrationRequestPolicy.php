<?php

namespace App\Policies;

use App\Models\RegistrationRequest;
use App\Models\User;

class RegistrationRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    public function approve(User $user, RegistrationRequest $registrationRequest): bool
    {
        return $user->isManager();
    }

    public function reject(User $user, RegistrationRequest $registrationRequest): bool
    {
        return $user->isManager();
    }
}
