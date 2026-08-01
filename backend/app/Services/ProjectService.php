<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Enums\ProjectStatus;
use App\Notifications\ProjectSubmittedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function create(array $data, User $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['project_code'] = Project::generateProjectCode();
            $data['status'] = ProjectStatus::Draft;
            
            return Project::create($data);
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
            return $project->fresh();
        });
    }

    public function delete(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            return $project->delete();
        });
    }

    public function submit(Project $project): Project
    {
        return DB::transaction(function () use ($project) {
            if (!$project->canTransitionTo(ProjectStatus::Submitted)) {
                throw new \Exception('Invalid status transition');
            }

            $oldStatus = $project->status;
            
            $project->status = ProjectStatus::Submitted;
            $project->submitted_at = now();
            $project->save();

            $project->reviews()->create([
                'status_from' => $oldStatus,
                'status_to' => ProjectStatus::Submitted,
                'notes' => 'Project submitted for review',
                'reviewed_at' => now(),
            ]);

            $penilais = User::role('penilai')->get();
            Notification::send($penilais, new ProjectSubmittedNotification($project));

            return $project;
        });
    }

    public function list(array $filters, User $user): LengthAwarePaginator
    {
        $query = Project::with(['user', 'documentCategory', 'currentReviewer'])
            ->withCount(['documents', 'reviews']);

        if ($user->hasRole('pemohon')) {
            $query->forUser($user->id);
        } elseif ($user->hasRole('penilai')) {
            $query->excludeDraft();
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('document_category_id', $filters['category_id']);
        }

        $query->dateBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null);

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
