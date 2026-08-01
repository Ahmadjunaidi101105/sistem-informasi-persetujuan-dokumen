<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    public function created(Project $project): void
    {
        $this->invalidateDashboardCache($project);
    }

    public function updated(Project $project): void
    {
        if ($project->isDirty('status') || $project->isDirty('current_reviewer_id')) {
            $this->invalidateDashboardCache($project);
        }
    }

    public function deleted(Project $project): void
    {
        $this->invalidateDashboardCache($project);
    }

    private function invalidateDashboardCache(Project $project): void
    {
        // Flush all dashboard cache or specifically pemohon cache
        Cache::tags(['dashboard'])->flush();
    }
}
