<?php

namespace Tests\Traits;

use App\Models\DocumentCategory;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

trait TestHelpers
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function createPemohon(array $attributes = []): User
    {
        $user = User::factory()->pemohon()->create($attributes);
        $user->assignRole('pemohon');
        return $user;
    }

    protected function createPenilai(array $attributes = []): User
    {
        $user = User::factory()->penilai()->create($attributes);
        $user->assignRole('penilai');
        return $user;
    }

    protected function createProjectWithDocuments(
        User $user,
        string $status = 'draft',
        int $docCount = 1
    ): Project {
        $category = DocumentCategory::factory()->create();

        $factoryMethod = match ($status) {
            'draft' => 'draft',
            'submitted' => 'submitted',
            'in_review' => 'inReview',
            'approved' => 'approved',
            'revised' => 'revised',
            'rejected' => 'rejected',
            default => 'draft',
        };

        $project = Project::factory()->{$factoryMethod}()->create([
            'user_id' => $user->id,
            'document_category_id' => $category->id,
        ]);

        if ($docCount > 0) {
            ProjectDocument::factory()->count($docCount)->create([
                'project_id' => $project->id,
                'uploaded_by' => $user->id,
            ]);
        }

        return $project;
    }
}
