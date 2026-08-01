<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectDocumentFactory extends Factory
{
    public function definition(): array
    {
        $types = [
            ['original_name' => 'dokumen_permohonan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'surat_pernyataan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'akta_perusahaan.pdf', 'mime_type' => 'application/pdf'],
            ['original_name' => 'foto_lokasi.jpg', 'mime_type' => 'image/jpeg'],
            ['original_name' => 'denah_bangunan.png', 'mime_type' => 'image/png'],
            ['original_name' => 'laporan_teknis.docx', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        ];

        $type = fake()->randomElement($types);

        return [
            'project_id' => Project::factory(),
            'file_name' => fake()->uuid() . '.' . pathinfo($type['original_name'], PATHINFO_EXTENSION),
            'original_name' => $type['original_name'],
            'file_path' => 'documents/' . fake()->uuid() . '/' . $type['original_name'],
            'file_size' => fake()->numberBetween(100000, 5000000),
            'mime_type' => $type['mime_type'],
            'version' => 1,
            'uploaded_by' => User::factory(),
        ];
    }
}
