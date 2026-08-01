<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Models\DocumentCategory;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    protected ProjectPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
        $this->policy = new ProjectPolicy();
    }

    // --- view ---

    public function test_pemohon_can_view_own_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->assertTrue($this->policy->view($pemohon, $project));
    }

    public function test_pemohon_cannot_view_others_project(): void
    {
        $pemohon = $this->createPemohon();
        $other = $this->createPemohon(['email' => 'other@test.com']);
        $project = $this->createProjectWithDocuments($other, 'draft', 0);

        $this->assertFalse($this->policy->view($pemohon, $project));
    }

    // --- update ---

    public function test_pemohon_can_update_own_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->assertTrue($this->policy->update($pemohon, $project));
    }

    public function test_pemohon_can_update_own_revised_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'revised', 0);

        $this->assertTrue($this->policy->update($pemohon, $project));
    }

    public function test_pemohon_cannot_update_submitted_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $this->assertFalse($this->policy->update($pemohon, $project));
    }

    public function test_pemohon_cannot_update_approved_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'approved', 0);

        $this->assertFalse($this->policy->update($pemohon, $project));
    }

    // --- delete ---

    public function test_pemohon_can_delete_own_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->assertTrue($this->policy->delete($pemohon, $project));
    }

    public function test_pemohon_cannot_delete_submitted_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $this->assertFalse($this->policy->delete($pemohon, $project));
    }

    // --- penilai view ---

    public function test_penilai_can_view_all_non_draft_projects(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $this->assertTrue($this->policy->view($penilai, $project));
    }

    public function test_penilai_cannot_view_draft_projects_of_others(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->assertFalse($this->policy->view($penilai, $project));
    }

    // --- penilai review ---

    public function test_penilai_can_review_project_assigned_to_them(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 0);
        $project->current_reviewer_id = $penilai->id;
        $project->save();

        $this->assertTrue($this->policy->review($penilai, $project));
    }

    public function test_penilai_cannot_review_project_assigned_to_another(): void
    {
        $pemohon = $this->createPemohon();
        $penilai1 = $this->createPenilai(['email' => 'penilai1@test.com']);
        $penilai2 = $this->createPenilai(['email' => 'penilai2@test.com']);
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 0);
        $project->current_reviewer_id = $penilai1->id;
        $project->save();

        $this->assertFalse($this->policy->review($penilai2, $project));
    }
}
