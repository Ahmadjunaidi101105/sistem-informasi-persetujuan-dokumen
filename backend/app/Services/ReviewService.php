<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectApprovedNotification;
use App\Notifications\ProjectRejectedNotification;
use App\Notifications\ProjectRevisedNotification;
use App\Notifications\ProjectTakenForReviewNotification;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Take a project for review by a penilai.
     *
     * @param Project $project The project to review.
     * @param User $reviewer The penilai taking the review.
     * @return Project
     * @throws \Exception
     */
    public function takeReview(Project $project, User $reviewer): Project
    {
        return DB::transaction(function () use ($project, $reviewer) {
            $lockedProject = Project::where('id', $project->id)->lockForUpdate()->first();

            if (!$lockedProject->canTransitionTo(ProjectStatus::InReview)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $lockedProject->status;

            $lockedProject->status = ProjectStatus::InReview;
            $lockedProject->current_reviewer_id = $reviewer->id;
            $lockedProject->save();

            $lockedProject->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::InReview,
                'notes' => 'Project taken for review',
                'reviewed_at' => now(),
            ]);

            $lockedProject->user->notify(new ProjectTakenForReviewNotification($lockedProject));

            return $lockedProject;
        });
    }

    /**
     * Approve a project.
     *
     * @param Project $project The project to approve.
     * @param User $reviewer The reviewer approving the project.
     * @param string|null $notes Optional notes for approval.
     * @return Project
     * @throws \Exception
     */
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

            $project->user->notify(new ProjectApprovedNotification($project));

            return $project;
        });
    }

    /**
     * Request revision for a project.
     *
     * @param Project $project The project to revise.
     * @param User $reviewer The reviewer requesting revision.
     * @param string $notes Required notes explaining the revision.
     * @return Project
     * @throws \Exception
     */
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

            $project->user->notify(new ProjectRevisedNotification($project));

            return $project;
        });
    }

    /**
     * Reject a project.
     *
     * @param Project $project The project to reject.
     * @param User $reviewer The reviewer rejecting the project.
     * @param string $notes Required notes explaining the rejection.
     * @return Project
     * @throws \Exception
     */
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

            $project->user->notify(new ProjectRejectedNotification($project));

            return $project;
        });
    }
}
