<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectTakenForReviewNotification extends Notification implements ShouldQueue
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
            'type' => 'project_in_review',
            'title' => 'Permohonan Dinilai',
            'message' => "Permohonan dengan kode {$this->project->project_code} sedang dalam proses penilaian.",
            'project_id' => $this->project->id,
            'project_code' => $this->project->project_code,
        ];
    }
}
