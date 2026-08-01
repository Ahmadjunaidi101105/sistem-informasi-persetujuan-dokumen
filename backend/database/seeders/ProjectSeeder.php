<?php

namespace Database\Seeders;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $pemohonIds = User::role('pemohon')->pluck('id')->toArray();
        $penilaiIds = User::role('penilai')->pluck('id')->toArray();
        $categoryIds = DocumentCategory::pluck('id')->toArray();

        $statusDistribution = [
            'draft'     => 1000,
            'submitted' => 1500,
            'in_review' => 1000,
            'approved'  => 4000,
            'revised'   => 1500,
            'rejected'  => 1000,
        ];

        $documentTemplates = [
            ['original_name' => 'dokumen_permohonan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'surat_pernyataan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'akta_perusahaan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'foto_lokasi.jpg', 'mime_type' => 'image/jpeg'],
            ['original_name' => 'denah_bangunan.png', 'mime_type' => 'image/png'],
            ['original_name' => 'laporan_analisis.docx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['original_name' => 'sertifikat_tanah.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'proposal_teknis.pdf', 'mime_type' => 'application/pdf'],
        ];

        $priorities = ['low', 'normal', 'high'];

        $chunkSize = 500;
        $year2024Count = 4000;
        $year2025Count = 6000;
        $totalProjects = $year2024Count + $year2025Count;
        $projectId = 1;
        $documentId = 1;
        $reviewId = 1;

        $projects = [];
        $documents = [];
        $reviews = [];

        echo "Seeding 10,000 projects with documents and reviews...\n";

        DB::beginTransaction();

        try {
            foreach ($statusDistribution as $status => $count) {
                echo "Seeding {$count} projects with status '{$status}'...\n";
                for ($i = 0; $i < $count; $i++) {
                    $year = ($projectId <= $year2024Count) ? 2024 : 2025;
                    $seq = ($year == 2024) ? $projectId : ($projectId - $year2024Count);
                    $projectCode = sprintf('PRJ-%d-%05d', $year, $seq);

                    $createdAt = Carbon::now()->subDays(rand(1, 730));
                    $submittedAt = null;
                    $reviewedAt = null;
                    $approvedAt = null;
                    $rejectedAt = null;
                    $currentReviewerId = null;

                    $pemohonId = $pemohonIds[array_rand($pemohonIds)];
                    
                    if ($status !== 'draft') {
                        $submittedAt = $createdAt->copy()->addHours(rand(1, 48));
                        $reviews[] = [
                            'id' => $reviewId++,
                            'project_id' => $projectId,
                            'reviewer_id' => $penilaiIds[array_rand($penilaiIds)], // Should realistically be pemohon for submit but db schema asks for reviewer_id. We'll use a penilai for simplicity or null if allowed, but schema says NOT NULL.
                            'status_from' => 'draft',
                            'status_to' => 'submitted',
                            'notes' => null,
                            'reviewed_at' => $submittedAt->toDateTimeString(),
                            'created_at' => $submittedAt->toDateTimeString(),
                            'updated_at' => $submittedAt->toDateTimeString(),
                        ];

                        // Gen Docs
                        $numDocs = rand(1, 5);
                        for ($d = 0; $d < $numDocs; $d++) {
                            $template = $documentTemplates[array_rand($documentTemplates)];
                            $documents[] = [
                                'id' => $documentId++,
                                'project_id' => $projectId,
                                'file_name' => Str::uuid() . '.' . pathinfo($template['original_name'], PATHINFO_EXTENSION),
                                'original_name' => $template['original_name'],
                                'file_path' => 'documents/' . Str::uuid() . '/' . $template['original_name'],
                                'file_size' => rand(100000, 5000000),
                                'mime_type' => $template['mime_type'],
                                'version' => 1,
                                'uploaded_by' => $pemohonId,
                                'created_at' => $createdAt->toDateTimeString(),
                                'updated_at' => $createdAt->toDateTimeString(),
                            ];
                        }
                    } else {
                        // Draft docs (50% chance)
                        if (rand(0, 1)) {
                            $numDocs = rand(1, 3);
                            for ($d = 0; $d < $numDocs; $d++) {
                                $template = $documentTemplates[array_rand($documentTemplates)];
                                $documents[] = [
                                    'id' => $documentId++,
                                    'project_id' => $projectId,
                                    'file_name' => Str::uuid() . '.' . pathinfo($template['original_name'], PATHINFO_EXTENSION),
                                    'original_name' => $template['original_name'],
                                    'file_path' => 'documents/' . Str::uuid() . '/' . $template['original_name'],
                                    'file_size' => rand(100000, 5000000),
                                    'mime_type' => $template['mime_type'],
                                    'version' => 1,
                                    'uploaded_by' => $pemohonId,
                                    'created_at' => $createdAt->toDateTimeString(),
                                    'updated_at' => $createdAt->toDateTimeString(),
                                ];
                            }
                        }
                    }

                    if (in_array($status, ['in_review', 'approved', 'revised', 'rejected'])) {
                        $reviewedAt = $submittedAt->copy()->addHours(rand(2, 72));
                        $reviewerId = $penilaiIds[array_rand($penilaiIds)];
                        
                        $reviews[] = [
                            'id' => $reviewId++,
                            'project_id' => $projectId,
                            'reviewer_id' => $reviewerId,
                            'status_from' => 'submitted',
                            'status_to' => 'in_review',
                            'notes' => null,
                            'reviewed_at' => $reviewedAt->toDateTimeString(),
                            'created_at' => $reviewedAt->toDateTimeString(),
                            'updated_at' => $reviewedAt->toDateTimeString(),
                        ];

                        if ($status === 'in_review') {
                            $currentReviewerId = $reviewerId;
                        }

                        if ($status === 'approved') {
                            $approvedAt = $reviewedAt->copy()->addHours(rand(1, 24));
                            $reviews[] = [
                                'id' => $reviewId++,
                                'project_id' => $projectId,
                                'reviewer_id' => $reviewerId,
                                'status_from' => 'in_review',
                                'status_to' => 'approved',
                                'notes' => 'Semua persyaratan terpenuhi. Disetujui.',
                                'reviewed_at' => $approvedAt->toDateTimeString(),
                                'created_at' => $approvedAt->toDateTimeString(),
                                'updated_at' => $approvedAt->toDateTimeString(),
                            ];
                        }

                        if ($status === 'revised') {
                            $revisedAt = $reviewedAt->copy()->addHours(rand(1, 24));
                            $reviews[] = [
                                'id' => $reviewId++,
                                'project_id' => $projectId,
                                'reviewer_id' => $reviewerId,
                                'status_from' => 'in_review',
                                'status_to' => 'revised',
                                'notes' => 'Lampiran foto lokasi kurang jelas, mohon upload ulang.',
                                'reviewed_at' => $revisedAt->toDateTimeString(),
                                'created_at' => $revisedAt->toDateTimeString(),
                                'updated_at' => $revisedAt->toDateTimeString(),
                            ];
                        }

                        if ($status === 'rejected') {
                            $rejectedAt = $reviewedAt->copy()->addHours(rand(1, 24));
                            $reviews[] = [
                                'id' => $reviewId++,
                                'project_id' => $projectId,
                                'reviewer_id' => $reviewerId,
                                'status_from' => 'in_review',
                                'status_to' => 'rejected',
                                'notes' => 'Dokumen tidak memenuhi persyaratan minimum yang ditetapkan.',
                                'reviewed_at' => $rejectedAt->toDateTimeString(),
                                'created_at' => $rejectedAt->toDateTimeString(),
                                'updated_at' => $rejectedAt->toDateTimeString(),
                            ];
                        }
                    }

                    $projects[] = [
                        'id' => $projectId++,
                        'project_code' => $projectCode,
                        'user_id' => $pemohonId,
                        'document_category_id' => $categoryIds[array_rand($categoryIds)],
                        'title' => 'Permohonan ' . Str::random(10),
                        'description' => 'Deskripsi permohonan ' . Str::random(20),
                        'status' => $status,
                        'priority' => $priorities[array_rand($priorities)],
                        'submitted_at' => $submittedAt?->toDateTimeString(),
                        'reviewed_at' => $reviewedAt?->toDateTimeString(),
                        'approved_at' => $approvedAt?->toDateTimeString(),
                        'rejected_at' => $rejectedAt?->toDateTimeString(),
                        'revision_count' => ($status === 'revised') ? rand(1, 3) : 0,
                        'current_reviewer_id' => $currentReviewerId,
                        'notes' => null,
                        'created_at' => $createdAt->toDateTimeString(),
                        'updated_at' => $createdAt->toDateTimeString(),
                    ];

                    // Chunk inserts
                    if (count($projects) >= $chunkSize) {
                        DB::table('projects')->insert($projects);
                        if (count($documents) > 0) DB::table('project_documents')->insert($documents);
                        if (count($reviews) > 0) DB::table('project_reviews')->insert($reviews);
                        $projects = [];
                        $documents = [];
                        $reviews = [];
                    }
                }
            }

            // Insert remainders
            if (count($projects) > 0) DB::table('projects')->insert($projects);
            if (count($documents) > 0) DB::table('project_documents')->insert($documents);
            if (count($reviews) > 0) DB::table('project_reviews')->insert($reviews);

            // Update sequences for PostgreSQL since we explicitly inserted IDs
            DB::statement("SELECT setval('projects_id_seq', (SELECT MAX(id) FROM projects))");
            DB::statement("SELECT setval('project_documents_id_seq', (SELECT MAX(id) FROM project_documents))");
            DB::statement("SELECT setval('project_reviews_id_seq', (SELECT MAX(id) FROM project_reviews))");

            DB::commit();
            echo "Successfully seeded {$totalProjects} projects.\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error seeding projects: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
