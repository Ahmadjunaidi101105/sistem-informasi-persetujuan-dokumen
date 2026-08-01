<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Enums\ProjectStatus;
use App\Models\ProjectDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    // --- Submit Flow ---

    public function test_pemohon_can_submit_draft_project_with_documents(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit");

        $response->assertOk();
        
        $project->refresh();
        $this->assertEquals(ProjectStatus::Submitted, $project->status);
        $this->assertNotNull($project->submitted_at);
    }

    public function test_pemohon_cannot_submit_draft_project_without_documents(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit");

        $response->assertStatus(403);
    }

    public function test_pemohon_cannot_submit_already_submitted_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit");

        $response->assertStatus(403);
    }

    public function test_submit_changes_status_to_submitted_and_sets_submitted_at(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);
        $this->assertNull($project->submitted_at);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit");

        $response->assertOk()
            ->assertJsonPath('success', true);
            
        $project->refresh();
        $this->assertEquals(ProjectStatus::Submitted, $project->status);
        $this->assertNotNull($project->submitted_at);
    }

    // --- Take Review Flow ---

    public function test_penilai_can_take_submitted_project_for_review(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/take-review");

        $response->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::InReview, $project->status);
        $this->assertEquals($penilai->id, $project->current_reviewer_id);
    }

    public function test_penilai_cannot_take_project_already_in_review(): void
    {
        $pemohon = $this->createPemohon();
        $penilai1 = $this->createPenilai(['email' => 'p1@test.com']);
        $penilai2 = $this->createPenilai(['email' => 'p2@test.com']);
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai1->id]);

        $response = $this->actingAs($penilai2, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/take-review");

        $response->assertStatus(403);
    }

    public function test_take_review_sets_current_reviewer_id(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/take-review");

        $project->refresh();
        $this->assertEquals($penilai->id, $project->current_reviewer_id);
    }

    // --- Approve Flow ---

    public function test_current_reviewer_can_approve_project(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/approve", [
                'notes' => 'Dokumen lengkap dan sesuai.',
            ]);

        $response->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Approved, $project->status);
        $this->assertNotNull($project->approved_at);
    }

    public function test_other_penilai_cannot_approve(): void
    {
        $pemohon = $this->createPemohon();
        $penilai1 = $this->createPenilai(['email' => 'p1@test.com']);
        $penilai2 = $this->createPenilai(['email' => 'p2@test.com']);
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai1->id]);

        $response = $this->actingAs($penilai2, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/approve", [
                'notes' => 'Dokumen lengkap dan sesuai.',
            ]);

        $response->assertStatus(403);
    }

    public function test_approve_sets_approved_at(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);
        $this->assertNull($project->approved_at);

        $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/approve", [
                'notes' => 'Disetujui.',
            ]);

        $project->refresh();
        $this->assertNotNull($project->approved_at);
    }

    // --- Revise Flow ---

    public function test_current_reviewer_can_request_revision_with_notes(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/revise", [
                'notes' => 'Tolong perbaiki dokumen A.',
            ]);

        $response->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Revised, $project->status);
    }

    public function test_revision_requires_notes_min_10_chars(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/revise", [
                'notes' => 'Kurang', // Less than 10 chars
            ]);

        $response->assertStatus(422);
    }

    public function test_revise_increments_revision_count(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id, 'revision_count' => 0]);

        $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/revise", [
                'notes' => 'Tolong perbaiki dokumen A.',
            ]);

        $project->refresh();
        $this->assertEquals(1, $project->revision_count);
    }

    public function test_pemohon_can_resubmit_revised_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'revised', 1);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit");

        $response->assertOk();
        
        $project->refresh();
        $this->assertEquals(ProjectStatus::Submitted, $project->status);
    }

    // --- Reject Flow ---

    public function test_current_reviewer_can_reject_with_notes(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/reject", [
                'notes' => 'Permohonan ditolak karena fiktif.',
            ]);

        $response->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Rejected, $project->status);
        $this->assertNotNull($project->rejected_at);
    }

    public function test_rejection_requires_notes_min_10_chars(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/reject", [
                'notes' => 'Ditolak', // Less than 10 chars
            ]);

        $response->assertStatus(422);
    }

    public function test_rejected_project_cannot_be_modified(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'rejected', 1);

        // Cannot update
        $this->actingAs($pemohon, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", ['title' => 'New Title'])
            ->assertStatus(403);
            
        // Cannot resubmit
        $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit")
            ->assertStatus(403);
    }

    // --- Full Cycle ---

    public function test_complete_flow_draft_submit_review_approve(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        // 1. Submit
        $this->actingAs($pemohon, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/submit")
            ->assertOk();

        // 2. Take Review
        $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/take-review")
            ->assertOk();

        // 3. Approve
        $this->actingAs($penilai, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/approve", [
                'notes' => 'Bagus sekali.'
            ])
            ->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Approved, $project->status);
    }

    public function test_revision_cycle_draft_submit_review_revise_resubmit_review_approve(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        // 1. Submit
        $this->actingAs($pemohon, 'sanctum')->postJson("/api/v1/projects/{$project->id}/submit")->assertOk();

        // 2. Take Review
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();

        // 3. Revise
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/revise", [
            'notes' => 'Perbaiki bagian B'
        ])->assertOk();

        // 4. Resubmit
        $this->actingAs($pemohon, 'sanctum')->postJson("/api/v1/projects/{$project->id}/submit")->assertOk();

        // 5. Take Review (again)
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();

        // 6. Approve
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/approve", [
            'notes' => 'Sudah diperbaiki.'
        ])->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Approved, $project->status);
        $this->assertEquals(1, $project->revision_count);
    }

    public function test_rejection_flow_draft_submit_review_reject(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        // 1. Submit
        $this->actingAs($pemohon, 'sanctum')->postJson("/api/v1/projects/{$project->id}/submit")->assertOk();

        // 2. Take Review
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();

        // 3. Reject
        $this->actingAs($penilai, 'sanctum')->postJson("/api/v1/projects/{$project->id}/reject", [
            'notes' => 'Permohonan tidak sah.'
        ])->assertOk();

        $project->refresh();
        $this->assertEquals(ProjectStatus::Rejected, $project->status);
    }
}
