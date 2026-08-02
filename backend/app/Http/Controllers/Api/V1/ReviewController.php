<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProjectReviewResource;
use App\Models\Project;
use App\Models\ProjectReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends BaseController
{
    public function index(Request $request)
    {
        if (!$request->user()->hasRole('penilai')) {
            return self::error('Unauthorized', 403);
        }

        // project.user is loaded because the history table shows the applicant.
        $query = ProjectReview::with(['project.user', 'reviewer']);

        if ($request->filled('reviewer_id')) {
            $query->where('reviewer_id', $request->reviewer_id);
        }

        if ($request->filled('status_to')) {
            $query->where('status_to', $request->status_to);
        }

        // The history page offers a search box; without this it did nothing.
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('project_code', 'ILIKE', "%{$search}%")
                    ->orWhere('title', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('reviewed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('reviewed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $reviews = $query->orderBy('reviewed_at', 'desc')->paginate($request->get('per_page', 15));
        $reviews->setCollection(
            $reviews->getCollection()->map(fn ($review) => new ProjectReviewResource($review))
        );

        return self::paginated($reviews);
    }

    public function projectReviews(Project $project)
    {
        Gate::authorize('view', $project);

        $reviews = $project->reviews()->with('reviewer')->orderBy('reviewed_at', 'desc')->get();

        return self::success(ProjectReviewResource::collection($reviews));
    }
}
