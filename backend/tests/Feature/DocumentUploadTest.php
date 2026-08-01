<?php

namespace Tests\Feature;

use App\Models\ProjectDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
        Storage::fake('local');
    }

    public function test_pemohon_can_upload_document_to_own_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $response = $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 200, 'application/pdf'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.original_name', 'izin.pdf');

        $this->assertDatabaseHas('project_documents', [
            'project_id' => $project->id,
            'original_name' => 'izin.pdf',
            'uploaded_by' => $pemohon->id,
        ]);
    }

    public function test_uploaded_file_is_stored_with_a_randomised_name(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('rahasia.pdf', 50, 'application/pdf'),
        ])->assertCreated();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();

        // The original name is preserved for display, but never used on disk.
        $this->assertSame('rahasia.pdf', $document->original_name);
        $this->assertNotSame('rahasia.pdf', $document->file_name);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_upload_is_rejected_for_disallowed_file_type(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'),
        ])->assertStatus(422)->assertJsonValidationErrors('document');
    }

    public function test_upload_is_rejected_when_file_exceeds_10mb(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            // Limit is 10240 KB.
            'document' => UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('document');
    }

    public function test_upload_requires_a_document(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)
            ->postJson("/api/v1/projects/{$project->id}/documents", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('document');
    }

    public function test_pemohon_cannot_upload_to_someone_elses_project(): void
    {
        $owner = $this->createPemohon();
        $intruder = $this->createPemohon(['email' => 'intruder@test.com']);
        $project = $this->createProjectWithDocuments($owner, 'draft', 0);

        $this->actingAs($intruder)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 50, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_upload_is_blocked_once_the_project_has_been_submitted(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('tambahan.pdf', 50, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_upload_is_allowed_again_while_the_project_is_revised(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'revised', 1);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('perbaikan.pdf', 50, 'application/pdf'),
        ])->assertCreated();
    }

    public function test_document_version_increments_per_upload(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        foreach (['a.pdf', 'b.pdf'] as $name) {
            $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
                'document' => UploadedFile::fake()->create($name, 20, 'application/pdf'),
            ])->assertCreated();
        }

        $this->assertSame([1, 2], ProjectDocument::where('project_id', $project->id)
            ->orderBy('version')
            ->pluck('version')
            ->all());
    }

    public function test_owner_can_download_own_document(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();

        $this->actingAs($pemohon)
            ->get("/api/v1/documents/{$document->id}/download")
            ->assertOk();
    }

    public function test_penilai_can_download_any_document(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();

        $this->actingAs($penilai)
            ->get("/api/v1/documents/{$document->id}/download")
            ->assertOk();
    }

    public function test_pemohon_cannot_download_another_pemohons_document(): void
    {
        $owner = $this->createPemohon();
        $intruder = $this->createPemohon(['email' => 'intruder@test.com']);
        $project = $this->createProjectWithDocuments($owner, 'draft', 0);

        $this->actingAs($owner)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();

        $this->actingAs($intruder)
            ->getJson("/api/v1/documents/{$document->id}/download")
            ->assertForbidden();
    }

    public function test_owner_can_delete_document_from_draft_project(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->actingAs($pemohon)->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 20, 'application/pdf'),
        ])->assertCreated();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();
        $path = $document->file_path;

        $this->actingAs($pemohon)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertOk();

        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_document_cannot_be_deleted_once_submitted(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
        $document = $project->documents()->firstOrFail();

        $this->actingAs($pemohon)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('project_documents', ['id' => $document->id]);
    }

    public function test_guest_cannot_upload(): void
    {
        $pemohon = $this->createPemohon();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 0);

        $this->postJson("/api/v1/projects/{$project->id}/documents", [
            'document' => UploadedFile::fake()->create('izin.pdf', 20, 'application/pdf'),
        ])->assertUnauthorized();
    }
}
