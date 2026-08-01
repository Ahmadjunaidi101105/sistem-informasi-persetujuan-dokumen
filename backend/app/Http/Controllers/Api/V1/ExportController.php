<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ExportController extends BaseController
{
    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'status', 'category_id', 'search', 'date_from', 'date_to'
        ]);

        $fileName = 'projects_export_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new ProjectsExport($filters, $request->user()), $fileName);
    }

    public function exportPdf(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $project->load([
            'user', 
            'documentCategory', 
            'documents', 
            'reviews.reviewer'
        ]);

        $pdf = Pdf::loadView('exports.project-detail', compact('project'));

        return $pdf->download("project-{$project->project_code}.pdf");
    }
}
