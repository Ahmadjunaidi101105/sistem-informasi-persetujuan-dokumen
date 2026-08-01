<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard statistics for pemohon.
     *
     * @param User $user The authenticated pemohon user.
     * @return array
     */
    public function getPemohonDashboard(User $user): array
    {
        return Cache::tags(['dashboard', 'pemohon', "user_{$user->id}"])->remember("dashboard:pemohon:{$user->id}", 300, function () use ($user) {
            $projects = Project::where('user_id', $user->id);

            $totalProjects = (clone $projects)->count();
            $statusDistribution = (clone $projects)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $recentProjects = (clone $projects)
                ->with('documentCategory')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $monthlyTrends = (clone $projects)
                ->select(
                    DB::raw('extract(month from created_at) as month'),
                    DB::raw('extract(year from created_at) as year'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            return [
                'total_projects' => $totalProjects,
                'status_distribution' => $statusDistribution,
                'recent_projects' => $recentProjects,
                'monthly_trends' => $monthlyTrends,
            ];
        });
    }

    /**
     * Get dashboard statistics for penilai.
     *
     * @param User $user The authenticated penilai user.
     * @return array
     */
    public function getPenilaiDashboard(User $user): array
    {
        return Cache::tags(['dashboard', 'penilai', "user_{$user->id}"])->remember("dashboard:penilai:{$user->id}", 300, function () use ($user) {
            $projects = Project::where('status', '!=', 'draft');

            $totalSubmissions = (clone $projects)->count();

            $statusDistribution = (clone $projects)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $approved = $statusDistribution[ProjectStatus::Approved->value] ?? 0;
            $totalProcessed = (clone $projects)->whereIn('status', ['approved', 'rejected', 'revised'])->count();

            $approvalRate = $totalProcessed > 0 ? round(($approved / $totalProcessed) * 100, 2) : 0;

            $categoryDistribution = (clone $projects)
                ->join('document_categories', 'projects.document_category_id', '=', 'document_categories.id')
                ->select('document_categories.name', DB::raw('count(projects.id) as count'))
                ->groupBy('document_categories.name')
                ->get();

            $monthlyTrends = (clone $projects)
                ->select(
                    'status',
                    DB::raw('extract(month from created_at) as month'),
                    DB::raw('extract(year from created_at) as year'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(6))
                ->whereIn('status', ['approved', 'rejected', 'revised'])
                ->groupBy('status', 'year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $recentReviews = Project::with('documentCategory')
                ->where('current_reviewer_id', $user->id)
                ->where('status', 'in_review')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            return [
                'total_submissions' => $totalSubmissions,
                'status_distribution' => $statusDistribution,
                'approval_rate' => $approvalRate,
                'category_distribution' => $categoryDistribution,
                'monthly_trends' => $monthlyTrends,
                'recent_reviews' => $recentReviews,
            ];
        });
    }
}
