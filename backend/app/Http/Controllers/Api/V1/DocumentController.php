<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UploadDocumentRequest;
use App\Http\Resources\Api\V1\ProjectDocumentResource;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends BaseController
{
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $documents = $project->documents()->with('uploadedBy')->get();
        return self::success(ProjectDocumentResource::collection($documents));
    }

    public function store(UploadDocumentRequest $request, Project $project)
    {
        Gate::authorize('store', [ProjectDocument::class, $project]);

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Generate unique filename
        $fileName = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('documents/' . $project->id, $fileName, 'local');

        $latestVersion = $project->documents()->max('version') ?? 0;

        $document = $project->documents()->create([
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'version' => $latestVersion + 1,
            'uploaded_by_id' => $request->user()->id,
        ]);

        $document->load('uploadedBy');

        return self::created(new ProjectDocumentResource($document), 'Dokumen berhasil diupload');
    }

    public function download(ProjectDocument $document)
    {
        Gate::authorize('download', $document);

        if (!Storage::disk('local')->exists($document->file_path)) {
            return self::error('File tidak ditemukan', 404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(ProjectDocument $document)
    {
        Gate::authorize('delete', $document);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return self::success(null, 'Dokumen berhasil dihapus');
    }
}
