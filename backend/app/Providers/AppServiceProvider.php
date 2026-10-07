<?php

namespace App\Providers;

use App\Models\RegistrationRequest;
use App\Models\Survey;
use App\Models\Task;
use App\Models\User;
use App\Policies\RegistrationRequestPolicy;
use App\Policies\SurveyPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(RegistrationRequest::class, RegistrationRequestPolicy::class);
        Gate::policy(Survey::class, SurveyPolicy::class);
    }
}
