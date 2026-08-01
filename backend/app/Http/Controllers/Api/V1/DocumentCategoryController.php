<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\DocumentCategoryResource;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Cache;

class DocumentCategoryController extends BaseController
{
    public function index()
    {
        $categories = Cache::remember('document_categories', 3600, function () {
            return DocumentCategory::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
        });

        return self::success(DocumentCategoryResource::collection($categories));
    }
}
