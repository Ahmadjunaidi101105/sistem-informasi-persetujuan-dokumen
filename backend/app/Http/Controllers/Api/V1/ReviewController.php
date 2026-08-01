<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Project;

class ReviewController extends BaseController
{
    public function index()
    {
        return self::success(null, 'Not implemented');
    }

    public function projectReviews(Project $project)
    {
        return self::success(null, 'Not implemented');
    }
}
