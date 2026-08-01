<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function pemohon(Request $request)
    {
        if (!$request->user()->hasRole('pemohon')) {
            return self::error('Unauthorized', 403);
        }

        $data = $this->dashboardService->getPemohonDashboard($request->user());
        return self::success($data);
    }

    public function penilai(Request $request)
    {
        if (!$request->user()->hasRole('penilai')) {
            return self::error('Unauthorized', 403);
        }

        $data = $this->dashboardService->getPenilaiDashboard($request->user());
        return self::success($data);
    }
}
