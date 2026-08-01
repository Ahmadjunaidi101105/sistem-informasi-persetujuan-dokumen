<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\DocumentCategoryResource;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends BaseController
{
    public function index()
    {
        $categories = DocumentCategory::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return self::success(DocumentCategoryResource::collection($categories));
    }
}
