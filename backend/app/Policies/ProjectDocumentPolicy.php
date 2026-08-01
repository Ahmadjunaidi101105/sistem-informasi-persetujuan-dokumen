<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;

class ProjectDocumentPolicy
{
    public function store(User $user, Project $project): bool
    {
        return $user->hasRole('pemohon')
            && $user->id === $project->user_id
            && in_array($project->status, ['draft', 'revised']);
    }

    public function download(User $user, ProjectDocument $document): bool
    {
        if ($user->hasRole('pemohon')) {
            return $user->id === $document->project->user_id;
        }

        if ($user->hasRole('penilai')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, ProjectDocument $document): bool
    {
        return $user->hasRole('pemohon')
            && $user->id === $document->project->user_id
            && in_array($document->project->status, ['draft', 'revised']);
    }
}
