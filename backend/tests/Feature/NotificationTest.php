<?php

namespace Tests\Feature;

use App\Notifications\ProjectApprovedNotification;
use App\Notifications\ProjectRejectedNotification;
use App\Notifications\ProjectRevisedNotification;
use App\Notifications\ProjectSubmittedNotification;
use App\Notifications\ProjectTakenForReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    public function test_submitting_a_project_notifies_penilai(): void
    {
        Notification::fake();

        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'draft', 1);

        $this->actingAs($pemohon)
            ->postJson("/api/v1/projects/{$project->id}/submit")
            ->assertOk();

        Notification::assertSentTo($penilai, ProjectSubmittedNotification::class);
    }

    public function test_taking_a_project_for_review_notifies_the_owner(): void
    {
        Notification::fake();

        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/take-review")
            ->assertOk();

        Notification::assertSentTo($pemohon, ProjectTakenForReviewNotification::class);
    }

    public function test_approving_notifies_the_owner(): void
    {
        Notification::fake();

        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/approve", ['notes' => 'Dokumen lengkap.'])
            ->assertOk();

        Notification::assertSentTo($pemohon, ProjectApprovedNotification::class);
    }

    public function test_requesting_revision_notifies_the_owner(): void
    {
        Notification::fake();

        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/revise", ['notes' => 'Mohon lengkapi lampiran.'])
            ->assertOk();

        Notification::assertSentTo($pemohon, ProjectRevisedNotification::class);
    }

    public function test_rejecting_notifies_the_owner(): void
    {
        Notification::fake();

        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/reject", ['notes' => 'Tidak memenuhi syarat.'])
            ->assertOk();

        Notification::assertSentTo($pemohon, ProjectRejectedNotification::class);
    }

    public function test_notification_payload_carries_a_link_to_the_project(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'in_review', 1);
        $project->update(['current_reviewer_id' => $penilai->id]);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/approve", ['notes' => 'Sesuai.'])
            ->assertOk();

        $notification = $pemohon->fresh()->notifications()->firstOrFail();

        // The notification list navigates using this field.
        $this->assertSame("/pemohon/projects/{$project->id}", $notification->data['link']);
        $this->assertSame($project->project_code, $notification->data['project_code']);
    }

    public function test_user_can_list_their_notifications(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/take-review")
            ->assertOk();

        $this->actingAs($pemohon)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [['id', 'type', 'data', 'read_at', 'created_at']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_notifications_are_scoped_to_the_authenticated_user(): void
    {
        $pemohon = $this->createPemohon();
        $stranger = $this->createPemohon(['email' => 'stranger@test.com']);
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)
            ->postJson("/api/v1/projects/{$project->id}/take-review")
            ->assertOk();

        $this->actingAs($stranger)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_mark_a_single_notification_as_read(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();

        $notification = $pemohon->fresh()->notifications()->firstOrFail();
        $this->assertNull($notification->read_at);

        $this->actingAs($pemohon)
            ->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $pemohon = $this->createPemohon();
        $stranger = $this->createPemohon(['email' => 'stranger@test.com']);
        $penilai = $this->createPenilai();
        $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);

        $this->actingAs($penilai)->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();

        $notification = $pemohon->fresh()->notifications()->firstOrFail();

        $this->actingAs($stranger)
            ->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        foreach (range(1, 2) as $i) {
            $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
            $this->actingAs($penilai)->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();
        }

        $this->assertSame(2, $pemohon->fresh()->unreadNotifications()->count());

        $this->actingAs($pemohon)
            ->postJson('/api/v1/notifications/read-all')
            ->assertOk();

        $this->assertSame(0, $pemohon->fresh()->unreadNotifications()->count());
    }

    public function test_unread_only_filter_returns_just_unread_notifications(): void
    {
        $pemohon = $this->createPemohon();
        $penilai = $this->createPenilai();

        foreach (range(1, 2) as $i) {
            $project = $this->createProjectWithDocuments($pemohon, 'submitted', 1);
            $this->actingAs($penilai)->postJson("/api/v1/projects/{$project->id}/take-review")->assertOk();
        }

        $pemohon->fresh()->notifications()->first()->markAsRead();

        $this->actingAs($pemohon)
            ->getJson('/api/v1/notifications?unread_only=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_notifications_require_authentication(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
    }
}
