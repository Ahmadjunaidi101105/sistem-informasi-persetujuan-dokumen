<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ReviewActionRequest;
use App\Http\Requests\Api\V1\ReviseRejectRequest;
use App\Http\Requests\Api\V1\StoreProjectRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectCollection;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends BaseController
{
    protected $projectService;
    protected $reviewService;

    public function __construct(ProjectService $projectService, ReviewService $reviewService)
    {
        $this->projectService = $projectService;
        $this->reviewService = $reviewService;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Project::class);

        $filters = $request->only([
            'page', 'per_page', 'status', 'category_id', 'search',
            'sort_by', 'sort_order', 'date_from', 'date_to',
        ]);

        $paginator = $this->projectService->list($filters, $request->user());
        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($project) => new ProjectResource($project))
        );

        return self::paginated($paginator);
    }

    public function store(StoreProjectRequest $request)
    {
        Gate::authorize('create', Project::class);

        $project = $this->projectService->create($request->validated(), $request->user());
        $project->load(['user', 'documentCategory']);

        return self::created(new ProjectResource($project), 'Project berhasil dibuat');
    }

    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load([
            'user',
            'documentCategory',
            'currentReviewer',
            'documents.uploader',
            'reviews.reviewer',
        ]);
        $project->loadCount(['documents', 'reviews']);

        return self::success(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $updatedProject = $this->projectService->update($project, $request->validated());
        $updatedProject->load(['user', 'documentCategory']);

        return self::success(new ProjectResource($updatedProject), 'Project berhasil diupdate');
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $this->projectService->delete($project);

        return self::success(null, 'Project berhasil dihapus');
    }

    public function submit(Request $request, Project $project)
    {
        Gate::authorize('submit', $project);

        $updatedProject = $this->projectService->submit($project);

        return self::success(new ProjectResource($updatedProject), 'Project berhasil disubmit');
    }

    public function takeReview(Request $request, Project $project)
    {
        Gate::authorize('takeReview', $project);

        $updatedProject = $this->reviewService->takeReview($project, $request->user());

        return self::success(new ProjectResource($updatedProject), 'Project berhasil diambil untuk review');
    }

    public function approve(ReviewActionRequest $request, Project $project)
    {
        Gate::authorize('approve', $project);

        $updatedProject = $this->reviewService->approve($project, $request->user(), $request->notes);

        return self::success(new ProjectResource($updatedProject), 'Project berhasil disetujui');
    }

    public function revise(ReviseRejectRequest $request, Project $project)
    {
        Gate::authorize('revise', $project);

        $updatedProject = $this->reviewService->revise($project, $request->user(), $request->notes);

        return self::success(new ProjectResource($updatedProject), 'Project dikembalikan untuk revisi');
    }

    public function reject(ReviseRejectRequest $request, Project $project)
    {
        Gate::authorize('reject', $project);

        $updatedProject = $this->reviewService->reject($project, $request->user(), $request->notes);

        return self::success(new ProjectResource($updatedProject), 'Project ditolak');
    }
}
