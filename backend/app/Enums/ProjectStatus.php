<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Revised = 'revised';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::InReview => 'In Review',
            self::Approved => 'Approved',
            self::Revised => 'Revised',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::InReview => 'cyan',
            self::Approved => 'green',
            self::Revised => 'amber',
            self::Rejected => 'red',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Draft => in_array($newStatus, [self::Submitted]),
            self::Submitted => in_array($newStatus, [self::InReview]),
            self::InReview => in_array($newStatus, [self::Approved, self::Revised, self::Rejected]),
            self::Revised => in_array($newStatus, [self::Submitted]),
            self::Approved, self::Rejected => false,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected]);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Revised]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
