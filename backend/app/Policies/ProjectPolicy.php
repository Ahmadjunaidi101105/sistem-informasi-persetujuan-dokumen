<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('pemohon')) {
            return $user->id === $project->user_id;
        }

        if ($user->hasRole('penilai')) {
            // Penilai bisa lihat semua kecuali draft milik orang lain
            if ($project->status === 'draft' && $project->user_id !== $user->id) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('pemohon');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole('pemohon') 
            && $user->id === $project->user_id 
            && in_array($project->status, ['draft', 'revised']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('pemohon') 
            && $user->id === $project->user_id 
            && $project->status === 'draft';
    }

    public function submit(User $user, Project $project): bool
    {
        return $user->hasRole('pemohon') 
            && $user->id === $project->user_id 
            && in_array($project->status, ['draft', 'revised'])
            && $project->documents()->count() > 0;
    }

    public function takeReview(User $user, Project $project): bool
    {
        return $user->hasRole('penilai') 
            && $project->status === 'submitted' 
            && is_null($project->current_reviewer_id);
    }

    public function review(User $user, Project $project): bool
    {
        return $user->hasRole('penilai') 
            && $project->status === 'in_review' 
            && $project->current_reviewer_id === $user->id;
    }

    public function approve(User $user, Project $project): bool
    {
        return $this->review($user, $project);
    }

    public function revise(User $user, Project $project): bool
    {
        return $this->review($user, $project);
    }

    public function reject(User $user, Project $project): bool
    {
        return $this->review($user, $project);
    }
}
