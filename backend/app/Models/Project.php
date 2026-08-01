<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code', 'user_id', 'document_category_id',
        'title', 'description', 'status', 'priority',
        'submitted_at', 'reviewed_at', 'approved_at', 'rejected_at',
        'revision_count', 'current_reviewer_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function currentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_reviewer_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class)->orderByDesc('reviewed_at');
    }

    // Scopes
    public function scopeStatus($query, ProjectStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeExcludeDraft($query)
    {
        return $query->where('status', '!=', ProjectStatus::Draft);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'ILIKE', "%{$search}%")
              ->orWhere('project_code', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%");
        });
    }

    public function scopeDateBetween($query, ?string $from, ?string $to)
    {
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to . ' 23:59:59');
        return $query;
    }

    // Helpers
    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function canTransitionTo(ProjectStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isReviewedBy(User $user): bool
    {
        return $this->current_reviewer_id === $user->id;
    }

    // Auto-generate project code
    public static function generateProjectCode(): string
    {
        $year = now()->year;
        $lastProject = static::where('project_code', 'LIKE', "PRJ-{$year}-%")
            ->orderByDesc('project_code')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($lastProject) {
            $lastSequence = (int) substr($lastProject->project_code, -5);
            $sequence = $lastSequence + 1;
        }

        return sprintf('PRJ-%d-%05d', $year, $sequence);
    }
}
