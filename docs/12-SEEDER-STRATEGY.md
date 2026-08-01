# Seeder Strategy — SIPDOK

## Overview
Total data yang harus di-seed: 10.000 projects, 1.000 pemohon, 1.000 penilai.
Proses seeding harus cepat dan menghasilkan data yang realistis.

## 1. Execution Order

```bash
php artisan db:seed
# Urutan:
# 1. RolesAndPermissionsSeeder
# 2. DocumentCategorySeeder
# 3. UserSeeder
# 4. ProjectSeeder (includes documents & reviews)
```

## 2. RolesAndPermissionsSeeder

### Roles
```php
$roles = ['pemohon', 'penilai'];
```

### Permissions
```php
$pemohonPermissions = [
    'project.create',
    'project.read.own',
    'project.update.own.draft',
    'project.submit',
    'project.resubmit',
    'document.upload',
    'document.download.own',
    'history.read.own',
    'dashboard.pemohon',
];

$penilaiPermissions = [
    'project.read.all',
    'project.review',
    'project.approve',
    'project.revise',
    'project.reject',
    'review.create',
    'history.read.all',
    'document.download.all',
    'dashboard.penilai',
];
```

## 3. DocumentCategorySeeder

```php
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
```

## 4. UserSeeder

### Test Accounts (must remember credentials)
```php
// Pemohon test account
User::create([
    'name' => 'Pemohon Test',
    'email' => 'pemohon@sipdok.test',
    'password' => Hash::make('password'),
    'company_name' => 'PT Test Pemohon Indonesia',
    'company_address' => 'Jl. Test No. 1, Jakarta',
    'phone' => '081234567890',
])->assignRole('pemohon');

// Penilai test account
User::create([
    'name' => 'Penilai Test',
    'email' => 'penilai@sipdok.test',
    'password' => Hash::make('password'),
    'company_name' => null,
    'company_address' => null,
    'phone' => '081234567891',
])->assignRole('penilai');
```

### Bulk Users (Factory)
```php
// 999 Pemohon (1 test + 999 factory = 1000)
User::factory()
    ->count(999)
    ->pemohon()
    ->create()
    ->each(fn ($user) => $user->assignRole('pemohon'));

// 999 Penilai (1 test + 999 factory = 1000)
User::factory()
    ->count(999)
    ->penilai()
    ->create()
    ->each(fn ($user) => $user->assignRole('penilai'));
```

### UserFactory States
```php
// Pemohon state
public function pemohon(): static
{
    return $this->state(fn () => [
        'name' => fake('id_ID')->name(),
        'email' => fake()->unique()->safeEmail(),
        'company_name' => 'PT ' . fake('id_ID')->company(),
        'company_address' => fake('id_ID')->address(),
        'phone' => fake('id_ID')->phoneNumber(),
    ]);
}

// Penilai state
public function penilai(): static
{
    return $this->state(fn () => [
        'name' => fake('id_ID')->name(),
        'email' => fake()->unique()->safeEmail(),
        'company_name' => null,
        'company_address' => null,
        'phone' => fake('id_ID')->phoneNumber(),
    ]);
}
```

## 5. ProjectSeeder (10.000 Projects)

### Performance Strategy
Gunakan chunk insert untuk kecepatan:
```php
// Jangan gunakan factory()->create() satu per satu untuk 10k records
// Gunakan insert batch dengan chunks

$chunkSize = 500;
$totalProjects = 10000;

for ($i = 0; $i < $totalProjects / $chunkSize; $i++) {
    $projects = [];
    for ($j = 0; $j < $chunkSize; $j++) {
        $projects[] = $this->generateProjectData($seq++);
    }
    Project::insert($projects);
}
```

### Status Distribution
```php
$statusDistribution = [
    'draft'     => 1000,  // 10%
    'submitted' => 1500,  // 15%
    'in_review' => 1000,  // 10%
    'approved'  => 4000,  // 40%
    'revised'   => 1500,  // 15%
    'rejected'  => 1000,  // 10%
];
```

### Project Code Generation
```php
// Format: PRJ-{YYYY}-{SEQ:5}
// Distribute across 2 years: 2024-2025
private function generateProjectCode(int $year, int $seq): string
{
    return sprintf('PRJ-%d-%05d', $year, $seq);
}

// 40% projects in 2024, 60% in 2025
```

### Timestamp Logic
```php
// Timestamps harus logis dan sequential
$created_at  = Carbon::now()->subDays(rand(1, 730));  // 2 tahun terakhir
$submitted_at = $created_at->copy()->addHours(rand(1, 48));
$reviewed_at  = $submitted_at->copy()->addHours(rand(2, 72));
$approved_at  = $reviewed_at->copy()->addHours(rand(1, 24));
$rejected_at  = $reviewed_at->copy()->addHours(rand(1, 24));
```

### Project Assignment
```php
// Setiap project di-assign ke random pemohon
$pemohonIds = User::role('pemohon')->pluck('id')->toArray();
$penilaiIds = User::role('penilai')->pluck('id')->toArray();

// user_id = random pemohon
// current_reviewer_id = random penilai (only for in_review status)
```

## 6. ProjectDocument Seeder (embedded in ProjectSeeder)

```php
// 1-5 dokumen dummy per project (kecuali draft yang mungkin 0)
// Tidak perlu file fisik, cukup metadata di database

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

// Per project:
$numDocs = rand(1, 5);
// draft: 50% chance 0 docs, 50% chance 1-3 docs
// non-draft: always 1-5 docs (must have at least 1 to be submitted)
```

## 7. ProjectReview Seeder (embedded in ProjectSeeder)

```php
// Review logs tergantung status project:

// draft: 0 reviews
// submitted: 1 review (created → submitted)
// in_review: 2 reviews (created → submitted → in_review)
// approved: 2-3 reviews (may include revision cycle)
// revised: 2-3 reviews (submitted → in_review → revised)
// rejected: 2-3 reviews

// Template review logs:
$reviewTemplates = [
    // Submit log
    [
        'status_from' => 'draft',
        'status_to' => 'submitted',
        'notes' => null,
    ],
    // Take review log
    [
        'status_from' => 'submitted',
        'status_to' => 'in_review',
        'notes' => null,
    ],
    // Approve log
    [
        'status_from' => 'in_review',
        'status_to' => 'approved',
        'notes' => fake()->randomElement([
            'Dokumen sudah lengkap dan memenuhi syarat.',
            'Semua persyaratan terpenuhi. Disetujui.',
            'Permohonan sesuai ketentuan yang berlaku.',
            null,
        ]),
    ],
    // Revise log
    [
        'status_from' => 'in_review',
        'status_to' => 'revised',
        'notes' => fake()->randomElement([
            'Dokumen AMDAL perlu dilengkapi dengan analisis dampak terbaru.',
            'Surat pernyataan belum ditandatangani oleh direktur.',
            'Lampiran foto lokasi kurang jelas, mohon upload ulang.',
            'Data teknis pada halaman 5 tidak konsisten dengan lampiran.',
            'Perlu tambahan dokumen izin dari instansi terkait.',
        ]),
    ],
    // Reject log
    [
        'status_from' => 'in_review',
        'status_to' => 'rejected',
        'notes' => fake()->randomElement([
            'Dokumen tidak memenuhi persyaratan minimum yang ditetapkan.',
            'Permohonan tidak sesuai dengan peraturan yang berlaku.',
            'Data yang disampaikan tidak valid setelah verifikasi lapangan.',
            'Perusahaan tidak memiliki izin operasional yang diperlukan.',
        ]),
    ],
];
```

## 8. Verification Queries

Setelah seeding, jalankan query ini untuk verifikasi:

```sql
-- Total users per role
SELECT r.name, COUNT(*) FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
GROUP BY r.name;
-- Expected: pemohon=1000, penilai=1000

-- Total projects per status
SELECT status, COUNT(*) FROM projects GROUP BY status ORDER BY status;
-- Expected: sesuai distribusi di atas

-- Projects with documents
SELECT COUNT(DISTINCT project_id) FROM project_documents;
-- Expected: ~9000+ (most projects except some drafts)

-- Review logs count
SELECT COUNT(*) FROM project_reviews;
-- Expected: ~25000-30000

-- Category distribution
SELECT dc.name, COUNT(p.id)
FROM projects p
JOIN document_categories dc ON p.document_category_id = dc.id
GROUP BY dc.name ORDER BY COUNT(p.id) DESC;
-- Expected: roughly even distribution

-- Test accounts exist
SELECT email, name FROM users WHERE email LIKE '%@sipdok.test';
-- Expected: pemohon@sipdok.test, penilai@sipdok.test
```

## 9. Seeder Performance Target

| Operation | Target Time |
|-----------|------------|
| Roles & Permissions | < 2s |
| Document Categories | < 1s |
| Users (2000) | < 10s |
| Projects (10000) + Documents + Reviews | < 60s |
| **Total** | **< 90s** |

Tips untuk kecepatan:
- Gunakan `DB::table()->insert()` untuk bulk insert
- Chunk per 500 records
- Disable model events saat seeding (`Model::unguard()`)
- Wrap dalam transaction
- Disable query log: `DB::disableQueryLog()`
