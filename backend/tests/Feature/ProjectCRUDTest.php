<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\DocumentCategory;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ProjectCRUDTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    // --- Create ---

    public function test_pemohon_can_create_a_new_project(): void
    {
        $pemohon = $this->createPemohon();
        $category = DocumentCategory::factory()->create();

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson('/api/v1/projects', [
                'title' => 'Permohonan Izin Usaha',
                'document_category_id' => $category->id,
                'priority' => 'normal',
                'description' => 'Deskripsi permohonan',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('projects', [
            'title' => 'Permohonan Izin Usaha',
            'user_id' => $pemohon->id,
        ]);
    }

    public function test_project_is_created_with_draft_status(): void
    {
        $pemohon = $this->createPemohon();
        $category = DocumentCategory::factory()->create();

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson('/api/v1/projects', [
                'title' => 'Test Draft Status',
                'document_category_id' => $category->id,
                'priority' => 'normal',
            ]);

        $response->assertStatus(201);

        $project = Project::where('title', 'Test Draft Status')->first();
        $this->assertEquals(ProjectStatus::Draft, $project->status);
    }

    public function test_project_code_is_auto_generated(): void
    {
        $pemohon = $this->createPemohon();
        $category = DocumentCategory::factory()->create();

        $response = $this->actingAs($pemohon, 'sanctum')
            ->postJson('/api/v1/projects', [
                'title' => 'Test Auto Code',
                'document_category_id' => $category->id,
                'priority' => 'normal',
            ]);

        $response->assertStatus(201);

        $project = Project::where('title', 'Test Auto Code')->first();
        $this->assertMatchesRegularExpression(
            '/^PRJ-\d{4}-\d{5}$/',
            $project->project_code
        );
    }

    // --- List ---

    public function test_pemohon_can_view_own_projects_list(): void
    {
        $pemohon = $this->createPemohon();
        $this->createProjectWithDocuments($pemohon, 'draft', 0);
        $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects');

        $response->assertOk();

        $count = count($response->json('data'));
        $this->assertEquals(2, $count);
    }

    public function test_pemohon_cannot_see_others_projects(): void
    {
        $pemohon1 = $this->createPemohon(['email' => 'p1@test.com']);
        $pemohon2 = $this->createPemohon(['email' => 'p2@test.com']);

        $this->createProjectWithDocuments($pemohon1, 'draft', 0);
        $this->createProjectWithDocuments($pemohon2, 'draft', 0);

        $response = $this->actingAs($pemohon1, 'sanctum')
            ->getJson('/api/v1/projects');

        $response->assertOk();
        $count = count($response->json('data'));
        $this->assertEquals(1, $count);
    }

    // --- View ---

    public function test_pemohon_can_view_own_project_detail(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    // --- Update ---

    public function test_pemohon_can_update_own_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", [
                'title' => 'Updated Title',
                'priority' => 'high',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_pemohon_cannot_update_submitted_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->putJson("/api/v1/projects/{$project->id}", [
                'title' => 'Should Not Update',
            ]);

        $response->assertStatus(403);
    }

    // --- Delete ---

    public function test_pemohon_can_delete_own_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_pemohon_cannot_delete_non_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }

    // --- Role Enforcement ---

    public function test_penilai_cannot_create_a_project(): void
    {
        $penilai = $this->createPenilai();
        $category = DocumentCategory::factory()->create();

        $response = $this->actingAs($penilai, 'sanctum')
            ->postJson('/api/v1/projects', [
                'title' => 'Should Fail',
                'document_category_id' => $category->id,
                'priority' => 'normal',
            ]);

        $response->assertStatus(403);
    }

    // --- Pagination & Filters ---

    public function test_project_list_supports_pagination(): void
    {
        $pemohon = $this->createPemohon();
        $category = DocumentCategory::factory()->create();

        for ($i = 0; $i < 20; $i++) {
            Project::factory()->draft()->create([
                'user_id' => $pemohon->id,
                'document_category_id' => $category->id,
            ]);
        }

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects?per_page=5&page=1');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
    }

    public function test_project_list_supports_filtering_by_status(): void
    {
        $pemohon = $this->createPemohon();
        $this->createProjectWithDocuments($pemohon, 'draft', 0);
        $this->createProjectWithDocuments($pemohon, 'submitted', 0);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects?status=draft');

        $response->assertOk();
        $projects = $response->json('data');
        foreach ($projects as $project) {
            $this->assertEquals('draft', $project['status']);
        }
    }

    public function test_project_list_supports_search(): void
    {
        $pemohon = $this->createPemohon();
        $category = DocumentCategory::factory()->create();

        Project::factory()->draft()->create([
            'user_id' => $pemohon->id,
            'document_category_id' => $category->id,
            'title' => 'Permohonan Izin Spesial',
        ]);
        Project::factory()->draft()->create([
            'user_id' => $pemohon->id,
            'document_category_id' => $category->id,
            'title' => 'Dokumen Lainnya',
        ]);

        $response = $this->actingAs($pemohon, 'sanctum')
            ->getJson('/api/v1/projects?search=Spesial');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
