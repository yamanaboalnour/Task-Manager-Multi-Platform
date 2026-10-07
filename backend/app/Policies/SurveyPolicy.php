<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->isManager() || $survey->status === Survey::STATUS_PUBLISHED;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->isManager();
    }

    public function publish(User $user, Survey $survey): bool
    {
        return $user->isManager();
    }

    public function respond(User $user, Survey $survey): bool
    {
        return ! $user->isManager() && $survey->status === Survey::STATUS_PUBLISHED;
    }

    public function viewResponses(User $user, Survey $survey): bool
    {
        return $user->isManager();
    }
}
