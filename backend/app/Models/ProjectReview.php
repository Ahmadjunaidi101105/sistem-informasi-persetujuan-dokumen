<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'reviewer_id', 'status_from',
        'status_to', 'notes', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status_from' => ProjectStatus::class,
            'status_to' => ProjectStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
