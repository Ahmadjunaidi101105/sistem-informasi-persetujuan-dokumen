<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'reviewer_id' => User::factory()->penilai(),
            'status_from' => ProjectStatus::Submitted,
            'status_to' => ProjectStatus::InReview,
            'notes' => fake()->optional(0.5)->paragraph(),
            'reviewed_at' => now(),
        ];
    }
}
