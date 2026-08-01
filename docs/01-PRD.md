# PRD — SIPDOK (Sistem Informasi Persetujuan Dokumen)

## 1. Ringkasan Eksekutif

SIPDOK adalah aplikasi web full-stack untuk mengelola proses pengajuan, penilaian, dan persetujuan dokumen kelayakan di sebuah instansi pemerintah. Sistem ini menangani workflow multi-tahap dari pengajuan oleh Pemohon hingga keputusan akhir oleh Penilai, dengan kemampuan menyimpan ratusan ribu hingga jutaan data permohonan.

## 2. Tujuan Proyek

- Membangun sistem pengajuan dokumen digital yang efisien dan scalable
- Mengimplementasikan workflow approval multi-tahap (Draft → Submitted → In Review → Approved/Revised/Rejected)
- Menangani volume data besar (10.000 project, 2.000 user) dengan performa optimal
- Menyediakan dashboard analitik untuk monitoring dan pelaporan

## 3. Technology Stack

| Layer        | Teknologi                     |
|--------------|-------------------------------|
| Backend      | PHP 8.2+, Laravel 12          |
| Frontend     | Vue 3 (Composition API)       |
| Database     | PostgreSQL 16                 |
| Auth         | Laravel Sanctum (SPA Token)   |
| Permission   | Spatie Laravel-Permission     |
| State Mgmt   | Pinia                        |
| UI Framework | Tailwind CSS + Headless UI    |
| Charts       | ApexCharts (vue3-apexcharts)  |
| HTTP Client  | Axios                         |
| Cache        | Redis                         |
| Queue        | Laravel Queue (Redis driver)  |
| Testing      | PHPUnit + Pest (Backend), Vitest (Frontend) |
| Container    | Docker + Docker Compose       |
| CI/CD        | GitLab CI                     |
| Export       | Laravel Excel (Maatwebsite), DomPDF |
| File Storage | Laravel Storage (local/S3)    |

## 4. User Roles & Permissions

### 4.1 Pemohon Dokumen (Applicant)

| Permission                  | Deskripsi                                                    |
|-----------------------------|--------------------------------------------------------------|
| `project.create`            | Membuat project permohonan dokumen baru                      |
| `project.read.own`          | Melihat project milik sendiri                                |
| `project.update.own.draft`  | Mengubah project selama status masih Draft                   |
| `project.submit`            | Mengirim permohonan untuk penilaian (Draft → Submitted)      |
| `project.resubmit`          | Submit ulang setelah revisi (Revised → Submitted)            |
| `document.upload`           | Upload dokumen pada form permohonan                          |
| `document.download.own`     | Download dokumen milik sendiri                               |
| `history.read.own`          | Melihat riwayat penilaian & revisi project sendiri           |
| `dashboard.pemohon`         | Akses dashboard pemohon                                      |

### 4.2 Penilai Dokumen (Reviewer/Assessor)

| Permission                  | Deskripsi                                                    |
|-----------------------------|--------------------------------------------------------------|
| `project.read.all`          | Melihat seluruh permohonan yang diajukan                     |
| `project.review`            | Melakukan review/penilaian dokumen                           |
| `project.approve`           | Menyetujui permohonan (In Review → Approved)                 |
| `project.revise`            | Meminta revisi (In Review → Revised)                         |
| `project.reject`            | Menolak permohonan (In Review → Rejected)                    |
| `review.create`             | Memberikan catatan penilaian                                 |
| `history.read.all`          | Melihat histori seluruh penilaian                            |
| `document.download.all`     | Download dokumen dari semua permohonan                       |
| `dashboard.penilai`         | Akses dashboard penilai                                      |

## 5. Status Workflow (State Machine)

```
[Draft] → [Submitted] → [In Review] → [Approved] ✓ (FINAL)
                                      → [Revised]  → [Submitted] (loop)
                                      → [Rejected] ✗ (FINAL, bisa ajukan baru)
```

### Status Definitions

| Status      | Code        | Deskripsi                                        | Siapa yang set     |
|-------------|-------------|--------------------------------------------------|--------------------|
| Draft       | `draft`     | Permohonan baru dibuat, belum dikirim            | System (auto)      |
| Submitted   | `submitted` | Pemohon telah mengirim untuk dinilai             | Pemohon            |
| In Review   | `in_review` | Penilai sedang melakukan review                  | Penilai            |
| Approved    | `approved`  | Permohonan disetujui                             | Penilai            |
| Revised     | `revised`   | Penilai meminta perbaikan                        | Penilai            |
| Rejected    | `rejected`  | Permohonan ditolak                               | Penilai            |

### Transisi yang Valid

| Dari        | Ke          | Aktor    | Aksi                    |
|-------------|-------------|----------|-------------------------|
| Draft       | Submitted   | Pemohon  | Submit Permohonan       |
| Submitted   | In Review   | Penilai  | Mulai Review            |
| In Review   | Approved    | Penilai  | Setujui                 |
| In Review   | Revised     | Penilai  | Minta Revisi            |
| In Review   | Rejected    | Penilai  | Tolak                   |
| Revised     | Submitted   | Pemohon  | Submit Ulang Perbaikan  |

## 6. Fitur Utama

### 6.1 Authentication & Authorization
- Register (Pemohon only, Penilai dibuat oleh sistem/seeder)
- Login / Logout via Sanctum SPA
- Role-based access control via Spatie Permission
- CSRF protection + rate limiting

### 6.2 Master Data Management
- CRUD Users (view & manage)
- CRUD Document Categories/Types
- CRUD Project metadata

### 6.3 Pengajuan Permohonan (Pemohon)
- Create new project (auto status: Draft)
- Fill application form with project details
- Upload supporting documents (PDF, DOC, DOCX, JPG, PNG — max 10MB each)
- Edit draft projects
- Submit for review
- Resubmit after revision

### 6.4 Penilaian Dokumen (Penilai)
- View all submitted applications
- Take application for review (Submitted → In Review)
- Review documents and add notes/comments
- Decision: Approve, Request Revision, or Reject
- Add mandatory notes when requesting revision or rejecting

### 6.5 History & Approval Logs
- Complete audit trail per project
- Who did what, when, with what notes
- Filterable by date range, status, user
- Revision history with version tracking

### 6.6 Dashboard
- **Pemohon Dashboard:** Total projects by status, recent submissions, pending revisions
- **Penilai Dashboard:** Total applications by status, pending reviews, approval rate, monthly trends
- Charts: Bar chart (by status), Line chart (monthly trends), Pie chart (approval rate)

### 6.7 Export (Nilai Tambahan)
- Export project list to Excel (.xlsx)
- Export project detail to PDF
- Filterable export (by status, date range)

### 6.8 Notifications (Queue-based)
- Database notifications for status changes
- In-app notification bell with unread count
- Queued processing for performance

## 7. Data Volume Requirements

| Entitas              | Jumlah    |
|----------------------|-----------|
| User Pemohon         | 1.000     |
| User Penilai         | 1.000     |
| Project Permohonan   | 10.000    |
| Dokumen per Project  | 1-5       |
| Review Logs          | ~30.000+  |

## 8. Non-Functional Requirements

### 8.1 Performance
- API response time < 200ms untuk list endpoints
- API response time < 100ms untuk single resource
- Pagination pada semua list endpoints (default 15, max 100)
- Database indexing pada kolom yang sering di-query dan di-filter
- Eager loading untuk menghindari N+1 queries
- Redis caching untuk dashboard aggregation (TTL: 5 menit)

### 8.2 Security
- Sanctum SPA authentication
- CORS configuration
- Input validation pada semua endpoints
- File upload validation (type, size, malware scan)
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Vue auto-escape + sanitize)
- Rate limiting pada auth endpoints (5 attempts/minute)
- Authorization check pada setiap endpoint

### 8.3 Code Quality
- PSR-12 coding standard
- Laravel best practices & conventions
- Vue 3 Composition API style
- Comprehensive API documentation
- Meaningful Git commits & branching strategy

## 9. Bobot Penilaian Target

| Aspek                                      | Bobot | Target |
|--------------------------------------------|-------|--------|
| Desain Database PostgreSQL                 | 20%   | 100%   |
| Backend Laravel (REST API, workflow)       | 25%   | 100%   |
| Optimasi Performa                          | 20%   | 100%   |
| Frontend Vue (UI/UX, komponen)             | 25%   | 100%   |
| Dashboard & Visualisasi Data               | 5%    | 100%   |
| Kualitas Kode (PSR-12, dokumentasi)        | 5%    | 100%   |
| Git, README, Dokumentasi API               | 5%    | 100%   |
| **Total**                                  | **100%** | **100%** |
