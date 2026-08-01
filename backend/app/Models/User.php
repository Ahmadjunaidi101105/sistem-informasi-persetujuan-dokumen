<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone',
        'company_name', 'company_address', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function reviewedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'current_reviewer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class, 'reviewer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePemohon($query)
    {
        return $query->role('pemohon');
    }

    public function scopePenilai($query)
    {
        return $query->role('penilai');
    }

    // Helpers
    public function isPemohon(): bool
    {
        return $this->hasRole('pemohon');
    }

    public function isPenilai(): bool
    {
        return $this->hasRole('penilai');
    }
}
