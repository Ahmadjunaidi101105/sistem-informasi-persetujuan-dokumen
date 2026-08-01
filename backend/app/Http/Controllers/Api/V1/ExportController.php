<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Project;

class ExportController extends BaseController
{
    public function exportExcel(Request $request)
    {
        return self::success(null, 'Coming soon: Export Excel');
    }

    public function exportPdf(Request $request, Project $project)
    {
        return self::success(null, 'Coming soon: Export PDF');
    }
}
