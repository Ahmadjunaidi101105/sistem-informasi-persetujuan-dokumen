<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'project_code' => 'PRJ-' . date('Y') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => User::factory()->pemohon(),
            'document_category_id' => DocumentCategory::factory(),
            'title' => fake('id_ID')->sentence(4),
            'description' => fake('id_ID')->paragraph(2),
            'status' => ProjectStatus::Draft,
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'notes' => fake()->optional(0.3)->sentence(),
            'revision_count' => 0,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::Draft]);
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::InReview,
            'submitted_at' => now()->subHours(24),
            'reviewed_at' => now(),
            'current_reviewer_id' => User::factory()->penilai(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::Approved,
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now()->subDays(1),
            'approved_at' => now(),
        ]);
    }

    public function revised(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::Revised,
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now()->subDays(1),
            'revision_count' => fake()->numberBetween(1, 3),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::Rejected,
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now()->subDays(1),
            'rejected_at' => now(),
        ]);
    }
}
