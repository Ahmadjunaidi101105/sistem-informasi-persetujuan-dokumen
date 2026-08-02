<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class DashboardTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_pemohon_dashboard_returns_expected_structure(): void
    {
        $pemohon = $this->createPemohon();
        $this->createProjectWithDocuments($pemohon, 'draft', 1);

        $this->actingAs($pemohon)
            ->getJson('/api/v1/dashboard/pemohon')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'total_projects',
                        'pending_review',
                        'approved',
                        'needs_revision',
                        'rejected',
                    ],
                    'status_distribution',
                    'recent_projects',
                    'monthly_trends',
                ],
            ]);
    }

    public function test_pemohon_stats_count_only_their_own_projects(): void
    {
        $pemohon = $this->createPemohon();
        $other = $this->createPemohon(['email' => 'lain@test.com']);

        $this->createProjectWithDocuments($pemohon, 'draft', 1);
        $this->createProjectWithDocuments($pemohon, 'approved', 1);
        // Belongs to somebody else and must not leak into the totals.
        $this->createProjectWithDocuments($other, 'approved', 1);

        $this->actingAs($pemohon)
            ->getJson('/api/v1/dashboard/pemohon')
            ->assertOk()
            ->assertJsonPath('data.stats.total_projects', 2)
            ->assertJsonPath('data.stats.approved', 1);
    }

    public function test_pemohon_recent_projects_are_shaped_like_the_project_resource(): void
    {
        $pemohon = $this->createPemohon();
        $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($pemohon)
            ->getJson('/api/v1/dashboard/pemohon')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'recent_projects' => [
                        ['id', 'project_code', 'title', 'status', 'status_label', 'created_at'],
                    ],
                ],
            ]);
    }

    public function test_penilai_cannot_access_pemohon_dashboard(): void
    {
        $penilai = $this->createPenilai();

        $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/pemohon')
            ->assertForbidden();
    }

    public function test_penilai_dashboard_returns_expected_structure(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'stats' => [
                        'total_submissions',
                        'pending_review',
                        'in_review',
                        'approved',
                        'rejected',
                    ],
                    'status_distribution',
                    'approval_rate',
                    'category_distribution',
                    'monthly_trends',
                    'recent_reviews',
                ],
            ]);
    }

    public function test_penilai_recent_reviews_lists_decisions_with_notes(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/approve", ['notes' => 'Dokumen sudah lengkap.'])
            ->assertOk();

        // docs/06-UI-DESIGN.md specifies this panel as the reviewer's recent
        // decisions with Keputusan and Catatan, not the berkas they hold.
        $response = $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'recent_reviews' => [
                        ['id', 'project_id', 'status_to', 'status_to_label', 'notes', 'reviewed_at', 'project' => ['project_code', 'title', 'user']],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.recent_reviews.0.status_to', 'approved');
        $response->assertJsonPath('data.recent_reviews.0.notes', 'Dokumen sudah lengkap.');
        $response->assertJsonPath('data.recent_reviews.0.project.user.name', $pemohon->name);
    }

    public function test_penilai_recent_reviews_excludes_take_review_and_other_reviewers(): void
    {
        $pemohon = $this->createPemohon();
        $mine = $this->createPenilai();
        $other = $this->createPenilai(['email' => 'penilai.lain@test.com']);

        // A take-review records an assignment, not a decision.
        $taken = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
        $this->actingAs($mine)->postJson("/api/v1/projects/{$taken->id}/take-review")->assertOk();

        // A decision made by somebody else must not appear here either.
        $theirs = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $theirs->update(['current_reviewer_id' => $other->id]);
        $this->actingAs($other)
            ->postJson("/api/v1/projects/{$theirs->id}/reject", ['notes' => 'Tidak memenuhi syarat.'])
            ->assertOk();

        $this->actingAs($mine)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonCount(0, 'data.recent_reviews');
    }

    public function test_penilai_dashboard_excludes_draft_projects(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        $this->createProjectWithDocuments($pemohon, 'draft', 1);
        $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        // Drafts are private to their owner and must not appear in the totals.
        $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonPath('data.stats.total_submissions', 1);
    }

    public function test_penilai_approval_rate_is_calculated_from_processed_projects(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        // 3 processed projects, 2 of which were approved.
        $this->createProjectWithDocuments($pemohon, 'approved', 1);
        $this->createProjectWithDocuments($pemohon, 'approved', 1);
        $this->createProjectWithDocuments($pemohon, 'rejected', 1);

        $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonPath('data.approval_rate', 66.67);
    }

    public function test_penilai_approval_rate_is_zero_when_nothing_processed(): void
    {
        $penilai = $this->createPenilai();

        $this->actingAs($penilai)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertOk()
            ->assertJsonPath('data.approval_rate', 0);
    }

    public function test_pemohon_cannot_access_penilai_dashboard(): void
    {
        $pemohon = $this->createPemohon();

        $this->actingAs($pemohon)
            ->getJson('/api/v1/dashboard/penilai')
            ->assertForbidden();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/v1/dashboard/pemohon')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/penilai')->assertUnauthorized();
    }
}
