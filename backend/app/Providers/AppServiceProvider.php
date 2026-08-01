<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Observers\ProjectObserver;
use App\Policies\ProjectDocumentPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectDocument::class, ProjectDocumentPolicy::class);

        Project::observe(ProjectObserver::class);

        // Backs $middleware->throttleApi(): 60 requests/minute, counted per
        // authenticated user so shared IPs do not throttle each other.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
