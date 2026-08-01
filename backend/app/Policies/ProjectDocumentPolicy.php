<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectDocumentPolicy
{
    public function store(User $user, Project $project): Response
    {
        if (!$user->hasRole('pemohon') || $user->id !== $project->user_id) {
            return Response::deny('Anda hanya dapat mengunggah dokumen pada permohonan milik sendiri.');
        }

        if (!in_array($project->status, [ProjectStatus::Draft, ProjectStatus::Revised])) {
            return Response::deny('Dokumen hanya dapat diunggah saat permohonan berstatus Draft atau Perlu Revisi.');
        }

        return Response::allow();
    }

    public function download(User $user, ProjectDocument $document): Response
    {
        if ($user->hasRole('penilai')) {
            return Response::allow();
        }

        if ($user->hasRole('pemohon') && $user->id === $document->project->user_id) {
            return Response::allow();
        }

        return Response::deny('Anda tidak memiliki akses untuk mengunduh dokumen ini.');
    }

    public function delete(User $user, ProjectDocument $document): Response
    {
        if (!$user->hasRole('pemohon') || $user->id !== $document->project->user_id) {
            return Response::deny('Anda hanya dapat menghapus dokumen pada permohonan milik sendiri.');
        }

        if (!in_array($document->project->status, [ProjectStatus::Draft, ProjectStatus::Revised])) {
            return Response::deny('Dokumen hanya dapat dihapus saat permohonan berstatus Draft atau Perlu Revisi.');
        }

        return Response::allow();
    }
}
