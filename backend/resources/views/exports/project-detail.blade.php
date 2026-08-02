<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Detail - {{ $project->project_code }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e40af; font-size: 24px; }
        .header p { margin: 5px 0 0 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; color: #374151; width: 30%; font-weight: bold; }
        .section-title { font-size: 16px; color: #1e40af; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; }
        .badge-draft { background-color: #f3f4f6; color: #374151; }
        .badge-submitted { background-color: #dbeafe; color: #1e40af; }
        .badge-in_review { background-color: #fef08a; color: #854d0e; }
        .badge-approved { background-color: #dcfce3; color: #166534; }
        .badge-revised { background-color: #ffedd5; color: #9a3412; }
        .badge-rejected { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <div class="header">
        <h1>SIPDOK</h1>
        <p>Sistem Informasi Persetujuan Dokumen</p>
    </div>

    <div class="section-title">Informasi Project</div>
    <table>
        <tr>
            <th>Kode Project</th>
            <td>{{ $project->project_code }}</td>
        </tr>
        <tr>
            <th>Judul</th>
            <td>{{ $project->title }}</td>
        </tr>
        <tr>
            <th>Kategori Dokumen</th>
            <td>{{ $project->documentCategory->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-{{ $project->status->value }}">
                    {{ $project->status->label() }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Prioritas</th>
            <td>{{ $project->priority?->label() ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pemohon</th>
            <td>{{ $project->user->name ?? '-' }} ({{ $project->user->company_name ?? '-' }})</td>
        </tr>
        <tr>
            <th>Deskripsi</th>
            <td>{{ $project->description ?: '-' }}</td>
        </tr>
        <tr>
            <th>Catatan Khusus</th>
            <td>{{ $project->notes ?: '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Diajukan</th>
            <td>{{ $project->submitted_at ? $project->submitted_at->format('d M Y H:i') : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Dokumen Lampiran ({{ $project->documents->count() }})</div>
    @if($project->documents->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Nama File</th>
                <th style="width: 20%;">Tipe</th>
                <th style="width: 25%;">Ukuran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->documents as $index => $doc)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $doc->original_name }} (v{{ $doc->version }})</td>
                <td>{{ $doc->mime_type }}</td>
                <td>{{ round($doc->file_size / 1024, 2) }} KB</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada dokumen yang dilampirkan.</p>
    @endif

    <div class="section-title">Riwayat Review ({{ $project->reviews->count() }})</div>
    @if($project->reviews->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Tanggal</th>
                <th style="width: 25%;">Penilai</th>
                <th style="width: 20%;">Keputusan</th>
                <th style="width: 35%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->reviews as $review)
            <tr>
                <td>{{ $review->reviewed_at->format('d M Y H:i') }}</td>
                <td>{{ $review->reviewer->name ?? '-' }}</td>
                {{-- status_from/status_to are cast to ProjectStatus by the model,
                     so they are already enum instances, not strings. --}}
                <td>
                    {{ $review->status_from?->label() }} &rarr;
                    <strong>{{ $review->status_to?->label() }}</strong>
                </td>
                <td>{{ $review->notes ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada riwayat review.</p>
    @endif

</body>
</html>
