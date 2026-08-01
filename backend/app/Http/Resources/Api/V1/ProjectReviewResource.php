<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectReviewResource extends JsonResource
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
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'title' => $this->project->title,
                ];
            }),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'status_from' => $this->status_from,
            'status_from_label' => ProjectStatus::tryFrom($this->status_from)?->label(),
            'status_to' => $this->status_to,
            'status_to_label' => ProjectStatus::tryFrom($this->status_to)?->label(),
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewed_at,
        ];
    }
}
