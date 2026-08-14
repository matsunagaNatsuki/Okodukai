<?php

namespace App\Providers;

use App\Models\Chore;
use App\Models\ChoreRecord;
use App\Models\User;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Chore::class, UserPolicy::class);
        Gate::policy(ChoreRecord::class, UserPolicy::class);
    }
}
