<?php

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;
use PHPUnit\Framework\TestCase;

class ProjectStatusTest extends TestCase
{
    // --- Valid transitions ---

    public function test_can_transition_from_draft_to_submitted(): void
    {
        $this->assertTrue(
            ProjectStatus::Draft->canTransitionTo(ProjectStatus::Submitted)
        );
    }

    public function test_can_transition_from_submitted_to_in_review(): void
    {
        $this->assertTrue(
            ProjectStatus::Submitted->canTransitionTo(ProjectStatus::InReview)
        );
    }

    public function test_can_transition_from_in_review_to_approved(): void
    {
        $this->assertTrue(
            ProjectStatus::InReview->canTransitionTo(ProjectStatus::Approved)
        );
    }

    public function test_can_transition_from_in_review_to_revised(): void
    {
        $this->assertTrue(
            ProjectStatus::InReview->canTransitionTo(ProjectStatus::Revised)
        );
    }

    public function test_can_transition_from_in_review_to_rejected(): void
    {
        $this->assertTrue(
            ProjectStatus::InReview->canTransitionTo(ProjectStatus::Rejected)
        );
    }

    public function test_can_transition_from_revised_to_submitted(): void
    {
        $this->assertTrue(
            ProjectStatus::Revised->canTransitionTo(ProjectStatus::Submitted)
        );
    }

    // --- Invalid transitions ---

    public function test_cannot_transition_from_draft_to_approved(): void
    {
        $this->assertFalse(
            ProjectStatus::Draft->canTransitionTo(ProjectStatus::Approved)
        );
    }

    public function test_cannot_transition_from_approved_to_any_status(): void
    {
        foreach (ProjectStatus::cases() as $status) {
            $this->assertFalse(
                ProjectStatus::Approved->canTransitionTo($status),
                "Approved should not transition to {$status->value}"
            );
        }
    }

    public function test_cannot_transition_from_rejected_to_any_status(): void
    {
        foreach (ProjectStatus::cases() as $status) {
            $this->assertFalse(
                ProjectStatus::Rejected->canTransitionTo($status),
                "Rejected should not transition to {$status->value}"
            );
        }
    }

    public function test_cannot_transition_from_submitted_to_approved_skipping_in_review(): void
    {
        $this->assertFalse(
            ProjectStatus::Submitted->canTransitionTo(ProjectStatus::Approved)
        );
    }

    // --- Helper methods ---

    public function test_is_final_returns_true_for_approved_and_rejected(): void
    {
        $this->assertTrue(ProjectStatus::Approved->isFinal());
        $this->assertTrue(ProjectStatus::Rejected->isFinal());
    }

    public function test_is_final_returns_false_for_non_final_statuses(): void
    {
        $this->assertFalse(ProjectStatus::Draft->isFinal());
        $this->assertFalse(ProjectStatus::Submitted->isFinal());
        $this->assertFalse(ProjectStatus::InReview->isFinal());
        $this->assertFalse(ProjectStatus::Revised->isFinal());
    }

    public function test_is_editable_returns_true_for_draft_and_revised(): void
    {
        $this->assertTrue(ProjectStatus::Draft->isEditable());
        $this->assertTrue(ProjectStatus::Revised->isEditable());
    }

    public function test_is_editable_returns_false_for_non_editable_statuses(): void
    {
        $this->assertFalse(ProjectStatus::Submitted->isEditable());
        $this->assertFalse(ProjectStatus::InReview->isEditable());
        $this->assertFalse(ProjectStatus::Approved->isEditable());
        $this->assertFalse(ProjectStatus::Rejected->isEditable());
    }

    public function test_label_returns_correct_string(): void
    {
        $this->assertEquals('Draft', ProjectStatus::Draft->label());
        $this->assertEquals('In Review', ProjectStatus::InReview->label());
        $this->assertEquals('Approved', ProjectStatus::Approved->label());
    }
}
