<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_submitted',
            'title' => 'Permohonan Baru',
            'message' => "Permohonan dengan kode {$this->project->project_code} telah diajukan dan menunggu penilaian.",
            'project_id' => $this->project->id,
            'project_code' => $this->project->project_code,
        ];
    }
}
