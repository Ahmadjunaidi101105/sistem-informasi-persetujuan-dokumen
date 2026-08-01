# Business Flow & Rules — SIPDOK

## 1. Alur Bisnis Utama (Business Process Flow)

### Flow 1: Pengajuan Baru (New Application)
```
Pemohon                              System                          Penilai
  │                                    │                               │
  ├─ Siapkan dokumen                   │                               │
  ├─ Login ke sistem ─────────────────►│                               │
  ├─ Isi Form Pengajuan ─────────────►│                               │
  │  (judul, kategori, deskripsi)      │                               │
  ├─ Upload dokumen pendukung ────────►│─── Validasi file              │
  │                                    │    (format, ukuran)           │
  ├─ Submit Permohonan ──────────────►│─── Status: Draft → Submitted  │
  │                                    │─── Generate project_code      │
  │                                    │─── Kirim notifikasi ─────────►│
  │                                    │                               │
  │                                    │                    Terima notifikasi
  │                                    │◄──────────── Take Review ─────┤
  │                                    │    Status: Submitted → In Review
  │  Terima notifikasi ◄──────────────│                               │
  │  "Sedang direview"                 │                               │
  │                                    │              Review dokumen ──┤
  │                                    │              Berikan catatan ─┤
  │                                    │                               │
  │                                    │              ┌─── KEPUTUSAN ──┤
  │                                    │              │                │
  │                                    │    ┌─── SETUJU (Approve)      │
  │                                    │    │    Status → Approved      │
  │  Terima notifikasi ◄──────────────│◄───┤    Dokumen terbit         │
  │  "Disetujui" ✓                     │    │                          │
  │                                    │    │                          │
  │                                    │    ├─── REVISI (Revise)       │
  │                                    │    │    Status → Revised       │
  │  Terima notifikasi ◄──────────────│◄───┤    + catatan revisi       │
  │  "Perlu revisi"                    │    │                          │
  │  Perbaiki & Submit ulang ─────────►│    │                          │
  │                                    │    │                          │
  │                                    │    └─── TOLAK (Reject)        │
  │                                    │         Status → Rejected     │
  │  Terima notifikasi ◄──────────────│◄────    + alasan penolakan     │
  │  "Ditolak" ✗                       │                               │
  │  Bisa buat pengajuan baru          │                               │
```

### Flow 2: Revisi (Revision Loop)
```
Pemohon                              System                          Penilai
  │                                    │                               │
  │  Terima notifikasi revisi          │                               │
  ├─ Login ke sistem                   │                               │
  ├─ Buka project yang direvisi        │                               │
  ├─ Lihat catatan revisi              │                               │
  ├─ Update dokumen/data ────────────►│                               │
  ├─ Upload dokumen baru (opsional)──►│─── Validasi file              │
  ├─ Submit Ulang ───────────────────►│─── Status: Revised → Submitted│
  │                                    │─── revision_count += 1        │
  │                                    │─── Kirim notifikasi ─────────►│
  │                                    │                               │
  │                                    │     (kembali ke flow review)  │
```

## 2. Business Rules

### BR-001: Project Code Generation
```
Format: PRJ-{YYYY}-{SEQUENTIAL:5}
Contoh: PRJ-2025-00001, PRJ-2025-00002
Rule: Sequential per tahun, auto-reset setiap tahun baru
Implementation: Database sequence atau MAX+1 dengan locking
```

### BR-002: Status Transition Rules
```
VALID TRANSITIONS:
  draft      → submitted     (by: Pemohon)
  submitted  → in_review     (by: Penilai)
  in_review  → approved      (by: Penilai)
  in_review  → revised       (by: Penilai)
  in_review  → rejected      (by: Penilai)
  revised    → submitted     (by: Pemohon)

INVALID TRANSITIONS (must be blocked):
  draft      → approved      ✗
  draft      → rejected      ✗
  submitted  → approved      ✗ (harus melalui in_review)
  approved   → *             ✗ (final state)
  rejected   → *             ✗ (final state, buat baru)
  in_review  → draft         ✗
  in_review  → submitted     ✗
```

### BR-003: Document Upload Rules
```
- Format yang diizinkan: PDF, DOC, DOCX, JPG, JPEG, PNG
- Ukuran maksimal per file: 10 MB
- Upload hanya bisa dilakukan saat status: draft atau revised
- Minimal 1 dokumen harus diupload sebelum submit
- Maksimal 10 dokumen per project
- Nama file disimpan dengan UUID untuk keamanan
- Original filename tetap disimpan di database
```

### BR-004: Review Assignment Rules
```
- Satu project hanya bisa direview oleh satu Penilai pada satu waktu
- Penilai mengambil project untuk direview (take review), bukan di-assign
- Jika project di-revise, current_reviewer_id di-reset ke null
- Penilai tidak bisa mereview project jika sudah diambil Penilai lain
```

### BR-005: Revision Rules
```
- Pemohon hanya bisa mengedit project saat status: draft atau revised
- Saat revisi, Penilai WAJIB memberikan catatan (notes) minimal 10 karakter
- Setiap revisi menambah revision_count
- Dokumen lama tetap tersimpan (versioning)
- Pemohon bisa upload dokumen baru atau update data
```

### BR-006: Rejection Rules
```
- Penilai WAJIB memberikan alasan penolakan (notes) minimal 10 karakter
- Setelah ditolak, Pemohon TIDAK bisa mengedit project tersebut
- Pemohon bisa membuat pengajuan baru (project baru)
- Project yang ditolak tetap tersimpan di histori
```

### BR-007: Approval Rules
```
- Hanya Penilai yang sedang mereview (current_reviewer) yang bisa approve
- Setelah approved, status menjadi final (tidak bisa diubah)
- approved_at timestamp dicatat
- Notifikasi dikirim ke Pemohon
```

### BR-008: Dashboard Data Rules
```
- Dashboard Pemohon: hanya menampilkan data project milik sendiri
- Dashboard Penilai: menampilkan data seluruh project (exclude draft)
- Data dashboard di-cache selama 5 menit
- Cache di-invalidate saat ada perubahan status project
```

### BR-009: Notification Rules
```
- Notifikasi dikirim saat:
  * Pemohon submit project → ke semua Penilai
  * Penilai take review → ke Pemohon pemilik project
  * Penilai approve → ke Pemohon
  * Penilai revise → ke Pemohon
  * Penilai reject → ke Pemohon
- Notifikasi disimpan di database (bukan email)
- Proses pengiriman notifikasi menggunakan Queue
- User bisa menandai notifikasi sebagai sudah dibaca
```

### BR-010: Data Access Rules
```
PEMOHON:
  - Hanya bisa melihat project milik sendiri
  - Hanya bisa mengedit project sendiri yang status draft/revised
  - Bisa download dokumen milik sendiri

PENILAI:
  - Bisa melihat semua project (kecuali yang masih draft)
  - Bisa download dokumen dari project manapun
  - Hanya bisa melakukan aksi review pada project yang diambilnya
  - Bisa melihat histori seluruh penilaian
```

### BR-011: Registration Rules
```
- Hanya Pemohon yang bisa register sendiri
- Akun Penilai dibuat oleh sistem (seeder/admin)
- company_name dan company_address wajib untuk Pemohon
- Email harus unik
- Password minimal 8 karakter
```

### BR-012: Export Rules
```
- Export Excel: list projects dengan filter yang sama seperti list API
- Export PDF: detail single project (informasi project + dokumen + histori review)
- Export dibatasi 5 request/menit per user
- Large exports diproses via Queue
```

## 3. Notification Templates

### Notif-001: Project Submitted
```
Title: "Permohonan Baru: {project_code}"
Message: "{user_name} dari {company_name} mengajukan permohonan dokumen '{title}'"
Target: Semua Penilai
```

### Notif-002: Project Taken for Review
```
Title: "Permohonan Sedang Direview"
Message: "Permohonan {project_code} sedang direview oleh {reviewer_name}"
Target: Pemohon (project owner)
```

### Notif-003: Project Approved
```
Title: "Permohonan Disetujui ✓"
Message: "Permohonan {project_code} - '{title}' telah disetujui"
Target: Pemohon (project owner)
```

### Notif-004: Project Revised
```
Title: "Permohonan Perlu Revisi"
Message: "Permohonan {project_code} memerlukan revisi. Catatan: {notes_preview}"
Target: Pemohon (project owner)
```

### Notif-005: Project Rejected
```
Title: "Permohonan Ditolak ✗"
Message: "Permohonan {project_code} - '{title}' ditolak. Alasan: {notes_preview}"
Target: Pemohon (project owner)
```

## 4. Validation Rules Summary

### Project Create/Update
```
title            → required, string, max:255
document_category_id → required, integer, exists:document_categories,id
description      → nullable, string, max:5000
priority         → nullable, in:low,normal,high
notes            → nullable, string, max:2000
```

### Document Upload
```
document         → required, file, mimes:pdf,doc,docx,jpg,jpeg,png, max:10240
```

### Review Actions (Approve)
```
notes            → nullable, string, max:2000
```

### Review Actions (Revise/Reject)
```
notes            → required, string, min:10, max:2000
```

### Register
```
name             → required, string, max:255
email            → required, email, unique:users, max:255
password         → required, string, min:8, confirmed
phone            → nullable, string, max:20
company_name     → required, string, max:255
company_address  → required, string, max:1000
```

### Login
```
email            → required, email
password         → required, string
```
