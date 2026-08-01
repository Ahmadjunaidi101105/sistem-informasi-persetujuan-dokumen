<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectDocument;

class DocumentController extends BaseController
{
    public function index(Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function store(Request $request, Project $project)
    {
        return self::created(null, 'Not implemented');
    }

    public function download(ProjectDocument $document)
    {
        return self::success(null, 'Not implemented');
    }

    public function destroy(ProjectDocument $document)
    {
        return self::success(null, 'Not implemented');
    }
}
