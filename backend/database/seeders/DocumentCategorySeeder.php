<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'AMDAL',           'name' => 'Analisis Mengenai Dampak Lingkungan'],
            ['code' => 'IMB',             'name' => 'Izin Mendirikan Bangunan'],
            ['code' => 'SIUP',            'name' => 'Surat Izin Usaha Perdagangan'],
            ['code' => 'TDP',             'name' => 'Tanda Daftar Perusahaan'],
            ['code' => 'UKL-UPL',         'name' => 'Upaya Pengelolaan & Pemantauan Lingkungan'],
            ['code' => 'IZIN-OPERASI',    'name' => 'Izin Operasional'],
            ['code' => 'HO',              'name' => 'Izin Gangguan (HO)'],
            ['code' => 'IZIN-LINGKUNGAN', 'name' => 'Izin Lingkungan'],
            ['code' => 'IPAL',            'name' => 'Izin Pembuangan Air Limbah'],
            ['code' => 'LAINNYA',         'name' => 'Dokumen Lainnya'],
        ];

        foreach ($categories as $category) {
            DocumentCategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
