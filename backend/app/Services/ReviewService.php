<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function takeReview(Project $project, User $reviewer): Project
    {
        return DB::transaction(function () use ($project, $reviewer) {
            if (!$project->canTransitionTo(ProjectStatus::InReview)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $project->status;
            
            $project->status = ProjectStatus::InReview;
            $project->current_reviewer_id = $reviewer->id;
            $project->save();

            $project->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::InReview,
                'notes' => 'Project taken for review',
                'reviewed_at' => now(),
            ]);

            return $project;
        });
    }

    public function approve(Project $project, User $reviewer, ?string $notes): Project
    {
        return DB::transaction(function () use ($project, $reviewer, $notes) {
            if (!$project->canTransitionTo(ProjectStatus::Approved)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $project->status;
            
            $project->status = ProjectStatus::Approved;
            $project->approved_at = now();
            $project->reviewed_at = now();
            $project->save();

            $project->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::Approved,
                'notes' => $notes,
                'reviewed_at' => now(),
            ]);

            return $project;
        });
    }

    public function revise(Project $project, User $reviewer, string $notes): Project
    {
        return DB::transaction(function () use ($project, $reviewer, $notes) {
            if (!$project->canTransitionTo(ProjectStatus::Revised)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $project->status;
            
            $project->status = ProjectStatus::Revised;
            $project->revision_count += 1;
            $project->current_reviewer_id = null;
            $project->reviewed_at = now();
            $project->save();

            $project->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::Revised,
                'notes' => $notes,
                'reviewed_at' => now(),
            ]);

            return $project;
        });
    }

    public function reject(Project $project, User $reviewer, string $notes): Project
    {
        return DB::transaction(function () use ($project, $reviewer, $notes) {
            if (!$project->canTransitionTo(ProjectStatus::Rejected)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $project->status;
            
            $project->status = ProjectStatus::Rejected;
            $project->rejected_at = now();
            $project->reviewed_at = now();
            $project->save();

            $project->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::Rejected,
                'notes' => $notes,
                'reviewed_at' => now(),
            ]);

            return $project;
        });
    }
}
