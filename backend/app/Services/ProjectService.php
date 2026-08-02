<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectSubmittedNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ProjectService
{
    /**
     * Columns the list endpoint may be ordered by. Mirrors the sortable
     * columns offered by the project and submission tables in the UI.
     */
    private const SORTABLE_COLUMNS = [
        'project_code',
        'title',
        'status',
        'priority',
        'created_at',
        'updated_at',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'revision_count',
    ];

    /**
     * Create a new project.
     *
     * @param array $data Project data.
     * @param User $user The user creating the project.
     * @return Project
     */
    public function create(array $data, User $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['project_code'] = Project::generateProjectCode();
            $data['status'] = ProjectStatus::Draft;

            return Project::create($data);
        });
    }

    /**
     * Update an existing project.
     *
     * @param Project $project The project to update.
     * @param array $data Updated project data.
     * @return Project
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
            return $project->fresh();
        });
    }

    /**
     * Delete a project.
     *
     * @param Project $project The project to delete.
     * @return bool
     */
    public function delete(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            return $project->delete();
        });
    }

    /**
     * Submit a draft project for review.
     *
     * @param Project $project The project to submit.
     * @return Project
     * @throws \Exception
     */
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
                'reviewer_id' => $project->user_id,
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

    /**
     * List projects with filters and pagination.
     *
     * @param array $filters Query filters.
     * @param User $user The authenticated user.
     * @return LengthAwarePaginator
     */
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

        // Sorting is restricted to a known set of columns: the value arrives
        // straight from a query string, and an unknown column made the endpoint
        // return a 500. The client sends "sort_order"; "sort_dir" is accepted
        // as an alias so older callers keep working.
        $sortBy = $filters['sort_by'] ?? 'created_at';
        if (!in_array($sortBy, self::SORTABLE_COLUMNS, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower((string) ($filters['sort_order'] ?? $filters['sort_dir'] ?? 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) ($filters['per_page'] ?? 15);
        // Guards against a caller asking for the whole table in one page.
        $perPage = max(1, min($perPage, 100));

        return $query->paginate($perPage);
    }
}
