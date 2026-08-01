# Database Design — SIPDOK

## 1. Entity Relationship Overview

```
users ──< projects ──< project_documents
  │           │
  │           └──< project_reviews (approval_logs)
  │                    │
  │                    └── reviewer (users)
  │
  ├── model_has_roles ──> roles
  ├── role_has_permissions ──> permissions
  └── notifications
```

## 2. Table Definitions

### 2.1 `users`

| Column             | Type                     | Constraints                              |
|--------------------|--------------------------|------------------------------------------|
| id                 | BIGSERIAL                | PRIMARY KEY                              |
| name               | VARCHAR(255)             | NOT NULL                                 |
| email              | VARCHAR(255)             | NOT NULL, UNIQUE                         |
| email_verified_at  | TIMESTAMP                | NULLABLE                                 |
| password           | VARCHAR(255)             | NOT NULL                                 |
| phone              | VARCHAR(20)              | NULLABLE                                 |
| company_name       | VARCHAR(255)             | NULLABLE (untuk Pemohon)                 |
| company_address    | TEXT                     | NULLABLE (untuk Pemohon)                 |
| avatar             | VARCHAR(255)             | NULLABLE                                 |
| is_active          | BOOLEAN                  | DEFAULT TRUE, NOT NULL                   |
| remember_token     | VARCHAR(100)             | NULLABLE                                 |
| created_at         | TIMESTAMP                | NOT NULL, DEFAULT NOW()                  |
| updated_at         | TIMESTAMP                | NOT NULL, DEFAULT NOW()                  |

**Indexes:**
- `idx_users_email` UNIQUE on (email)
- `idx_users_is_active` on (is_active)
- `idx_users_created_at` on (created_at)

---

### 2.2 `document_categories`

| Column      | Type            | Constraints                 |
|-------------|-----------------|-----------------------------|
| id          | BIGSERIAL       | PRIMARY KEY                 |
| name        | VARCHAR(255)    | NOT NULL, UNIQUE            |
| code        | VARCHAR(50)     | NOT NULL, UNIQUE            |
| description | TEXT            | NULLABLE                    |
| is_active   | BOOLEAN         | DEFAULT TRUE, NOT NULL      |
| created_at  | TIMESTAMP       | NOT NULL                    |
| updated_at  | TIMESTAMP       | NOT NULL                    |

**Indexes:**
- `idx_doc_categories_code` UNIQUE on (code)
- `idx_doc_categories_is_active` on (is_active)

---

### 2.3 `projects`

| Column               | Type            | Constraints                                   |
|----------------------|-----------------|-----------------------------------------------|
| id                   | BIGSERIAL       | PRIMARY KEY                                   |
| project_code         | VARCHAR(50)     | NOT NULL, UNIQUE                              |
| user_id              | BIGINT          | NOT NULL, FK → users(id)                      |
| document_category_id | BIGINT          | NOT NULL, FK → document_categories(id)        |
| title                | VARCHAR(255)    | NOT NULL                                      |
| description          | TEXT            | NULLABLE                                      |
| status               | VARCHAR(20)     | NOT NULL, DEFAULT 'draft'                     |
| priority             | VARCHAR(10)     | DEFAULT 'normal' (low/normal/high)            |
| submitted_at         | TIMESTAMP       | NULLABLE                                      |
| reviewed_at          | TIMESTAMP       | NULLABLE                                      |
| approved_at          | TIMESTAMP       | NULLABLE                                      |
| rejected_at          | TIMESTAMP       | NULLABLE                                      |
| revision_count       | INTEGER         | DEFAULT 0, NOT NULL                           |
| current_reviewer_id  | BIGINT          | NULLABLE, FK → users(id)                      |
| notes                | TEXT            | NULLABLE (catatan pemohon)                    |
| created_at           | TIMESTAMP       | NOT NULL                                      |
| updated_at           | TIMESTAMP       | NOT NULL                                      |

**Status CHECK constraint:** `status IN ('draft', 'submitted', 'in_review', 'approved', 'revised', 'rejected')`

**Priority CHECK constraint:** `priority IN ('low', 'normal', 'high')`

**Indexes:**
- `idx_projects_project_code` UNIQUE on (project_code)
- `idx_projects_user_id` on (user_id)
- `idx_projects_status` on (status)
- `idx_projects_document_category_id` on (document_category_id)
- `idx_projects_current_reviewer_id` on (current_reviewer_id)
- `idx_projects_created_at` on (created_at)
- `idx_projects_submitted_at` on (submitted_at)
- `idx_projects_status_created` on (status, created_at DESC) — composite untuk filtering + sorting
- `idx_projects_user_status` on (user_id, status) — composite untuk dashboard pemohon

---

### 2.4 `project_documents`

| Column        | Type            | Constraints                           |
|---------------|-----------------|---------------------------------------|
| id            | BIGSERIAL       | PRIMARY KEY                           |
| project_id    | BIGINT          | NOT NULL, FK → projects(id) ON DELETE CASCADE |
| file_name     | VARCHAR(255)    | NOT NULL                              |
| original_name | VARCHAR(255)    | NOT NULL                              |
| file_path     | VARCHAR(500)    | NOT NULL                              |
| file_size     | BIGINT          | NOT NULL (in bytes)                   |
| mime_type     | VARCHAR(100)    | NOT NULL                              |
| version       | INTEGER         | DEFAULT 1, NOT NULL                   |
| uploaded_by   | BIGINT          | NOT NULL, FK → users(id)              |
| created_at    | TIMESTAMP       | NOT NULL                              |
| updated_at    | TIMESTAMP       | NOT NULL                              |

**Indexes:**
- `idx_project_docs_project_id` on (project_id)
- `idx_project_docs_uploaded_by` on (uploaded_by)

---

### 2.5 `project_reviews` (Approval Logs / History)

| Column       | Type            | Constraints                                    |
|--------------|-----------------|------------------------------------------------|
| id           | BIGSERIAL       | PRIMARY KEY                                    |
| project_id   | BIGINT          | NOT NULL, FK → projects(id) ON DELETE CASCADE  |
| reviewer_id  | BIGINT          | NOT NULL, FK → users(id)                       |
| status_from  | VARCHAR(20)     | NOT NULL                                       |
| status_to    | VARCHAR(20)     | NOT NULL                                       |
| notes        | TEXT            | NULLABLE                                       |
| reviewed_at  | TIMESTAMP       | NOT NULL, DEFAULT NOW()                        |
| created_at   | TIMESTAMP       | NOT NULL                                       |
| updated_at   | TIMESTAMP       | NOT NULL                                       |

**Indexes:**
- `idx_reviews_project_id` on (project_id)
- `idx_reviews_reviewer_id` on (reviewer_id)
- `idx_reviews_reviewed_at` on (reviewed_at DESC)
- `idx_reviews_project_status` on (project_id, status_to) — composite untuk filter history
- `idx_reviews_reviewer_reviewed` on (reviewer_id, reviewed_at DESC) — composite untuk histori penilai

---

### 2.6 `notifications` (Laravel default)

| Column           | Type         | Constraints                     |
|------------------|--------------|---------------------------------|
| id               | UUID         | PRIMARY KEY                     |
| type             | VARCHAR(255) | NOT NULL                        |
| notifiable_type  | VARCHAR(255) | NOT NULL                        |
| notifiable_id    | BIGINT       | NOT NULL                        |
| data             | JSONB        | NOT NULL                        |
| read_at          | TIMESTAMP    | NULLABLE                        |
| created_at       | TIMESTAMP    | NOT NULL                        |
| updated_at       | TIMESTAMP    | NOT NULL                        |

**Indexes:**
- `idx_notifications_notifiable` on (notifiable_type, notifiable_id)
- `idx_notifications_read_at` on (read_at) WHERE read_at IS NULL (partial index)

---

### 2.7 Spatie Permission Tables (Auto-generated)

- `roles` (id, name, guard_name, created_at, updated_at)
- `permissions` (id, name, guard_name, created_at, updated_at)
- `model_has_roles` (role_id, model_type, model_id)
- `model_has_permissions` (permission_id, model_type, model_id)
- `role_has_permissions` (permission_id, role_id)

---

### 2.8 `personal_access_tokens` (Sanctum)

- Standard Sanctum migration (auto-generated)

---

### 2.9 `jobs` / `failed_jobs` (Queue)

- Standard Laravel queue migrations (auto-generated)

---

### 2.10 `cache` / `cache_locks` (Cache)

- Standard Laravel cache migrations (jika pakai database driver sebagai fallback)

## 3. Normalization Analysis

### 3NF Compliance
- **1NF:** Semua kolom bersifat atomic, tidak ada repeating groups
- **2NF:** Tidak ada partial dependencies (semua non-key attributes depend on full primary key)
- **3NF:** Tidak ada transitive dependencies
  - `document_categories` dipisahkan dari `projects` (bukan embedded string)
  - `project_reviews` terpisah sebagai tabel relasi bukan JSON field di projects
  - File metadata di `project_documents`, bukan di `projects`

## 4. Relationships

```
users (1) ──── (N) projects                    → Pemohon punya banyak project
users (1) ──── (N) projects.current_reviewer   → Penilai review banyak project
projects (1) ── (N) project_documents          → Project punya banyak dokumen
projects (1) ── (N) project_reviews            → Project punya banyak log review
users (1) ──── (N) project_reviews             → Penilai punya banyak review logs
users (N) ──── (N) roles                       → via model_has_roles (Spatie)
roles (N) ──── (N) permissions                 → via role_has_permissions (Spatie)
```

## 5. Seeder Strategy

### 5.1 Roles & Permissions Seeder
```
Roles: pemohon, penilai
Permissions: (lihat PRD section 4)
```

### 5.2 User Seeder
- 1.000 users dengan role `pemohon` (company_name & company_address terisi)
- 1.000 users dengan role `penilai`
- 1 admin test account per role:
  - pemohon@sipdok.test / password
  - penilai@sipdok.test / password

### 5.3 Document Categories Seeder
```
- AMDAL (Analisis Mengenai Dampak Lingkungan)
- IMB (Izin Mendirikan Bangunan)
- SIUP (Surat Izin Usaha Perdagangan)
- TDP (Tanda Daftar Perusahaan)
- UKL-UPL (Upaya Kelola & Pemantauan Lingkungan)
- IZIN-OPERASI (Izin Operasional)
- HO (Hinder Ordonnantie / Izin Gangguan)
- IZIN-LINGKUNGAN (Izin Lingkungan)
- IPAL (Izin Pembuangan Air Limbah)
- LAINNYA (Dokumen Lainnya)
```

### 5.4 Projects Seeder (10.000 records)
Distribusi status:
- Draft: 1.000 (10%)
- Submitted: 1.500 (15%)
- In Review: 1.000 (10%)
- Approved: 4.000 (40%)
- Revised: 1.500 (15%)
- Rejected: 1.000 (10%)

Setiap project:
- Randomly assigned ke salah satu pemohon
- project_code format: `PRJ-{YYYY}-{SEQ:5}` (contoh: PRJ-2025-00001)
- 1-5 dokumen dummy per project
- 1-5 review logs per project (kecuali draft)
- Timestamps realistis (distribusi 2 tahun terakhir)

### 5.5 Factory Pattern
Gunakan Laravel Factories + Faker untuk data realistis:
- Nama perusahaan Indonesia
- Alamat Indonesia
- Tanggal yang logis (submitted_at > created_at, reviewed_at > submitted_at, dst)

## 6. Migration Order

```
1. create_users_table
2. create_cache_table
3. create_jobs_table
4. create_personal_access_tokens_table (Sanctum)
5. create_permission_tables (Spatie)
6. create_document_categories_table
7. create_projects_table
8. create_project_documents_table
9. create_project_reviews_table
10. create_notifications_table
```

## 7. PostgreSQL-Specific Optimizations

### 7.1 Partial Indexes
```sql
-- Hanya index project yang belum selesai (sering di-query)
CREATE INDEX idx_projects_active ON projects (status, created_at DESC)
WHERE status NOT IN ('approved', 'rejected');

-- Notifikasi yang belum dibaca
CREATE INDEX idx_notifications_unread ON notifications (notifiable_id, created_at DESC)
WHERE read_at IS NULL;
```

### 7.2 Check Constraints
```sql
ALTER TABLE projects ADD CONSTRAINT chk_projects_status
CHECK (status IN ('draft', 'submitted', 'in_review', 'approved', 'revised', 'rejected'));

ALTER TABLE projects ADD CONSTRAINT chk_projects_priority
CHECK (priority IN ('low', 'normal', 'high'));

ALTER TABLE project_documents ADD CONSTRAINT chk_docs_file_size
CHECK (file_size > 0 AND file_size <= 10485760);  -- max 10MB
```

### 7.3 JSONB for Notifications
- PostgreSQL JSONB type untuk kolom `data` di notifications table
- Mendukung GIN index untuk pencarian di dalam notification data

### 7.4 Generated Column (Optional)
```sql
-- Full-text search pada projects
ALTER TABLE projects ADD COLUMN search_vector tsvector
GENERATED ALWAYS AS (
  to_tsvector('indonesian', coalesce(title, '') || ' ' || coalesce(description, '') || ' ' || coalesce(project_code, ''))
) STORED;

CREATE INDEX idx_projects_search ON projects USING GIN(search_vector);
```
