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
            // Exposed flat as well: the review history links straight to the
            // project, and relying on the nested object breaks when the
            // relation is not eager loaded.
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'title' => $this->project->title,
                    // The history table shows who filed the permohonan.
                    'user' => $this->project->relationLoaded('user')
                        ? new UserResource($this->project->user)
                        : null,
                ];
            }),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'status_from' => $this->status_from instanceof ProjectStatus ? $this->status_from->value : $this->status_from,
            'status_from_label' => $this->status_from instanceof ProjectStatus ? $this->status_from->label() : ProjectStatus::tryFrom($this->status_from)?->label(),
            'status_to' => $this->status_to instanceof ProjectStatus ? $this->status_to->value : $this->status_to,
            'status_to_label' => $this->status_to instanceof ProjectStatus ? $this->status_to->label() : ProjectStatus::tryFrom($this->status_to)?->label(),
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewed_at,
        ];
    }
}
