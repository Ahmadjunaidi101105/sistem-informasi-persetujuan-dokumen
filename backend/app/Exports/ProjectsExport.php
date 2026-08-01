<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;
    protected $user;
    private $rowNumber = 0;

    public function __construct(array $filters, User $user)
    {
        $this->filters = $filters;
        $this->user = $user;
    }

    public function query()
    {
        $query = Project::query()->with(['user', 'documentCategory']);

        if ($this->user->hasRole('pemohon')) {
            $query->forUser($this->user->id);
        } elseif ($this->user->hasRole('penilai')) {
            $query->excludeDraft();
        }

        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['priority'])) {
            $query->where('priority', $this->filters['priority']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('document_category_id', $this->filters['category_id']);
        }

        $query->dateBetween($this->filters['date_from'] ?? null, $this->filters['date_to'] ?? null);

        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Project Code',
            'Title',
            'Category',
            'Pemohon',
            'Company',
            'Status',
            'Priority',
            'Submitted At',
            'Created At',
        ];
    }

    public function map($project): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $project->project_code,
            $project->title,
            $project->documentCategory->name ?? '-',
            $project->user->name ?? '-',
            $project->user->company_name ?? '-',
            $project->status->label(),
            $project->priority?->label() ?? '-',
            $project->submitted_at ? $project->submitted_at->format('Y-m-d H:i:s') : '-',
            $project->created_at ? $project->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
