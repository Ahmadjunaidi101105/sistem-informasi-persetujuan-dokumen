<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends BaseController
{
    public function index()
    {
        return self::success(null, 'Not implemented');
    }

    public function store(Request $request)
    {
        return self::created(null, 'Not implemented');
    }

    public function show(Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function update(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function destroy(Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function submit(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function takeReview(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function approve(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function revise(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }

    public function reject(Request $request, Project $project)
    {
        return self::success(null, 'Not implemented');
    }
}
