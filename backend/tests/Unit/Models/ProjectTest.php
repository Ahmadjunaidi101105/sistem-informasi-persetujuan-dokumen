<?php

namespace Tests\Unit\Models;

use App\Enums\ProjectStatus;
use App\Models\DocumentCategory;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_generate_project_code_format_correct(): void
    {
        $code = Project::generateProjectCode();
        $year = now()->year;

        $this->assertMatchesRegularExpression(
            "/^PRJ-{$year}-\d{5}$/",
            $code
        );
    }

    public function test_generate_project_code_auto_increments(): void
    {
        $category = DocumentCategory::factory()->create();
        $user = $this->createPemohon();

        $project1 = Project::factory()->create([
            'user_id' => $user->id,
            'document_category_id' => $category->id,
            'project_code' => sprintf('PRJ-%d-%05d', now()->year, 1),
        ]);

        $code = Project::generateProjectCode();
        $this->assertEquals(
            sprintf('PRJ-%d-%05d', now()->year, 2),
            $code
        );
    }

    public function test_is_editable_returns_true_for_draft(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Draft;

        $this->assertTrue($project->isEditable());
    }

    public function test_is_editable_returns_true_for_revised(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Revised;

        $this->assertTrue($project->isEditable());
    }

    public function test_is_editable_returns_false_for_submitted(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Submitted;

        $this->assertFalse($project->isEditable());
    }

    public function test_is_editable_returns_false_for_in_review(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::InReview;

        $this->assertFalse($project->isEditable());
    }

    public function test_is_editable_returns_false_for_approved(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Approved;

        $this->assertFalse($project->isEditable());
    }

    public function test_is_editable_returns_false_for_rejected(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Rejected;

        $this->assertFalse($project->isEditable());
    }

    public function test_is_owned_by_returns_true_for_owner(): void
    {
        $user = $this->createPemohon();
        $project = $this->createProjectWithDocuments($user, 'draft', 0);

        $this->assertTrue($project->isOwnedBy($user));
    }

    public function test_is_owned_by_returns_false_for_non_owner(): void
    {
        $owner = $this->createPemohon();
        $other = $this->createPemohon(['email' => 'other@test.com']);
        $project = $this->createProjectWithDocuments($owner, 'draft', 0);

        $this->assertFalse($project->isOwnedBy($other));
    }

    public function test_can_transition_to_delegates_to_status_enum(): void
    {
        $project = new Project();
        $project->status = ProjectStatus::Draft;

        $this->assertTrue($project->canTransitionTo(ProjectStatus::Submitted));
        $this->assertFalse($project->canTransitionTo(ProjectStatus::Approved));
    }

    public function test_is_reviewed_by_returns_correct_values(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 0);
        $project->current_reviewer_id = $penilai->id;
        $project->save();

        $this->assertTrue($project->isReviewedBy($penilai));
        $this->assertFalse($project->isReviewedBy($pemohon));
    }
}
