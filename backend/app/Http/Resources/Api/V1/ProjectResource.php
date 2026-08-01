<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\ProjectStatus;
use App\Enums\ProjectPriority;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_code' => $this->project_code,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => ProjectStatus::tryFrom($this->status)?->label(),
            'status_color' => ProjectStatus::tryFrom($this->status)?->color(),
            'priority' => $this->priority,
            'priority_label' => ProjectPriority::tryFrom($this->priority)?->label(),
            'notes' => $this->notes,
            'submitted_at' => $this->submitted_at,
            'reviewed_at' => $this->reviewed_at,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'revision_count' => $this->revision_count,
            'user' => new UserResource($this->whenLoaded('user')),
            'document_category' => new DocumentCategoryResource($this->whenLoaded('documentCategory')),
            'current_reviewer' => new UserResource($this->whenLoaded('currentReviewer')),
            'documents_count' => $this->whenCounted('documents'),
            'reviews_count' => $this->whenCounted('reviews'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
