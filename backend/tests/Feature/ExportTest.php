<?php

namespace Tests\Feature;

use App\Exports\ProjectsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ExportTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_penilai_can_export_projects_to_excel(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $response = $this->actingAs($penilai)->get('/api/v1/export/projects');

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            (string) $response->headers->get('content-type')
        );
        $this->assertMatchesRegularExpression(
            '/attachment;\s*filename=.*projects_export_\d{8}_\d{6}\.xlsx/',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_pemohon_export_only_contains_their_own_projects(): void
    {
        $pemohon = $this->createPemohon();
        $other = $this->createPemohon(['email' => 'lain@test.com']);

        $mine = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
        $theirs = $this->createProjectWithDocuments($other, 'submitted', 1);

        $codes = (new ProjectsExport([], $pemohon))->query()->pluck('project_code');

        $this->assertTrue($codes->contains($mine->project_code));
        $this->assertFalse($codes->contains($theirs->project_code));
    }

    public function test_penilai_export_excludes_draft_projects(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        $draft = $this->createProjectWithDocuments($pemohon, 'draft', 1);
        $submitted = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $codes = (new ProjectsExport([], $penilai))->query()->pluck('project_code');

        $this->assertFalse($codes->contains($draft->project_code));
        $this->assertTrue($codes->contains($submitted->project_code));
    }

    public function test_export_honours_the_status_filter(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        $approved = $this->createProjectWithDocuments($pemohon, 'approved', 1);
        $rejected = $this->createProjectWithDocuments($pemohon, 'rejected', 1);

        $codes = (new ProjectsExport(['status' => 'approved'], $penilai))->query()->pluck('project_code');

        $this->assertTrue($codes->contains($approved->project_code));
        $this->assertFalse($codes->contains($rejected->project_code));
    }

    public function test_export_headings_match_the_mapped_columns(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
        $project->load(['user', 'documentCategory']);

        $export = new ProjectsExport([], $pemohon);

        // A mismatch here silently shifts every value under the wrong header.
        $this->assertCount(count($export->headings()), $export->map($project));
    }

    public function test_exported_row_contains_the_project_details(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
        $project->load(['user', 'documentCategory']);

        $row = (new ProjectsExport([], $pemohon))->map($project);

        $this->assertContains($project->project_code, $row);
        $this->assertContains($project->title, $row);
        $this->assertContains($pemohon->name, $row);
    }

    public function test_pemohon_can_export_own_project_to_pdf(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'approved', 1);

        $response = $this->actingAs($pemohon)->get("/api/v1/export/projects/{$project->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_pemohon_cannot_export_another_users_project_to_pdf(): void
    {
        $owner = $this->createPemohon();
        $intruder = $this->createPemohon(['email' => 'intruder@test.com']);
        $project = $this->createProjectWithDocuments($owner, 'approved', 1);

        $this->actingAs($intruder)
            ->getJson("/api/v1/export/projects/{$project->id}/pdf")
            ->assertForbidden();
    }

    public function test_penilai_can_export_any_submitted_project_to_pdf(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)
            ->get("/api/v1/export/projects/{$project->id}/pdf")
            ->assertOk();
    }

    public function test_export_requires_authentication(): void
    {
        $this->getJson('/api/v1/export/projects')->assertUnauthorized();
    }

    public function test_export_is_rate_limited_to_five_requests_per_minute(): void
    {
        $pemohon = $this->createPemohon();
        $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($pemohon)->get('/api/v1/export/projects')->assertOk();
        }

        $this->actingAs($pemohon)
            ->getJson('/api/v1/export/projects')
            ->assertStatus(429);
    }
}
