<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
            if ($project->status === ProjectStatus::Draft && $project->user_id !== $user->id) {
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

    public function update(User $user, Project $project): Response
    {
        if (!$user->hasRole('pemohon') || $user->id !== $project->user_id) {
            return Response::deny('Anda hanya dapat mengubah permohonan milik sendiri.');
        }

        if (!in_array($project->status, [ProjectStatus::Draft, ProjectStatus::Revised])) {
            return Response::deny('Permohonan hanya dapat diubah saat berstatus Draft atau Perlu Revisi.');
        }

        return Response::allow();
    }

    public function delete(User $user, Project $project): Response
    {
        if (!$user->hasRole('pemohon') || $user->id !== $project->user_id) {
            return Response::deny('Anda hanya dapat menghapus permohonan milik sendiri.');
        }

        if ($project->status !== ProjectStatus::Draft) {
            return Response::deny('Permohonan hanya dapat dihapus saat berstatus Draft.');
        }

        return Response::allow();
    }

    public function submit(User $user, Project $project): Response
    {
        if (!$user->hasRole('pemohon') || $user->id !== $project->user_id) {
            return Response::deny('Anda hanya dapat mengajukan permohonan milik sendiri.');
        }

        if (!in_array($project->status, [ProjectStatus::Draft, ProjectStatus::Revised])) {
            return Response::deny('Permohonan hanya dapat diajukan saat berstatus Draft atau Perlu Revisi.');
        }

        if ($project->documents()->count() === 0) {
            return Response::deny('Lampirkan minimal 1 dokumen sebelum mengajukan permohonan.');
        }

        return Response::allow();
    }

    public function takeReview(User $user, Project $project): Response
    {
        if (!$user->hasRole('penilai')) {
            return Response::deny('Hanya penilai yang dapat mengambil permohonan untuk dinilai.');
        }

        if ($project->status !== ProjectStatus::Submitted) {
            return Response::deny('Permohonan ini tidak sedang menunggu penilaian.');
        }

        if (!is_null($project->current_reviewer_id)) {
            return Response::deny('Permohonan ini sudah diambil oleh penilai lain.');
        }

        return Response::allow();
    }

    public function review(User $user, Project $project): Response
    {
        if (!$user->hasRole('penilai')) {
            return Response::deny('Hanya penilai yang dapat melakukan penilaian.');
        }

        if ($project->status !== ProjectStatus::InReview) {
            return Response::deny('Permohonan ini tidak sedang dalam proses penilaian.');
        }

        if ($project->current_reviewer_id !== $user->id) {
            return Response::deny('Anda hanya dapat menilai permohonan yang Anda ambil sendiri.');
        }

        return Response::allow();
    }

    public function approve(User $user, Project $project): Response
    {
        return $this->review($user, $project);
    }

    public function revise(User $user, Project $project): Response
    {
        return $this->review($user, $project);
    }

    public function reject(User $user, Project $project): Response
    {
        return $this->review($user, $project);
    }
}
