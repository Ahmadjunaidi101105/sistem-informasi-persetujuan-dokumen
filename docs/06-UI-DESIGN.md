# UI/UX Design Specification — SIPDOK

## 1. Design System

### 1.1 Color Palette
```
Primary:        #1E40AF (Blue-700)     — Brand, buttons, active states
Primary Light:  #3B82F6 (Blue-500)     — Hover, links
Primary Dark:   #1E3A5F                — Headers, sidebar
Secondary:      #059669 (Emerald-600)  — Success, approved
Warning:        #D97706 (Amber-600)    — Revised, pending
Danger:         #DC2626 (Red-600)      — Rejected, errors, delete
Info:           #0891B2 (Cyan-600)     — In review, info
Neutral:        #6B7280 (Gray-500)     — Draft, disabled
Background:     #F9FAFB (Gray-50)      — Page background
Surface:        #FFFFFF                — Cards, modals
Text Primary:   #111827 (Gray-900)     — Headings
Text Secondary: #6B7280 (Gray-500)     — Subtitles, hints
Border:         #E5E7EB (Gray-200)     — Borders, dividers
```

### 1.2 Status Color Mapping
```
draft       → Gray    (#6B7280)   — bg-gray-100 text-gray-700
submitted   → Blue    (#2563EB)   — bg-blue-100 text-blue-700
in_review   → Cyan    (#0891B2)   — bg-cyan-100 text-cyan-700
approved    → Green   (#059669)   — bg-emerald-100 text-emerald-700
revised     → Amber   (#D97706)   — bg-amber-100 text-amber-700
rejected    → Red     (#DC2626)   — bg-red-100 text-red-700
```

### 1.3 Typography
```
Font Family: Inter (Google Fonts)
Headings:    font-semibold
  H1: 24px / 1.5rem
  H2: 20px / 1.25rem
  H3: 16px / 1rem (bold)
Body:        14px / 0.875rem, font-normal
Small:       12px / 0.75rem
```

### 1.4 Spacing & Layout
```
Sidebar Width:   256px (16rem)
Content Max:     1280px
Card Padding:    24px (p-6)
Card Radius:     8px (rounded-lg)
Card Shadow:     shadow-sm
Gap:             16px (gap-4) for grid, 24px (gap-6) for sections
```

## 2. Page Layouts

### 2.1 Auth Layout (Login/Register)
```
┌─────────────────────────────────────────┐
│                                         │
│         ┌─────────────────────┐         │
│         │     SIPDOK Logo     │         │
│         │                     │         │
│         │   ┌─────────────┐   │         │
│         │   │ Email        │   │         │
│         │   └─────────────┘   │         │
│         │   ┌─────────────┐   │         │
│         │   │ Password     │   │         │
│         │   └─────────────┘   │         │
│         │                     │         │
│         │   [ Login Button ]  │         │
│         │                     │         │
│         │   Register link     │         │
│         └─────────────────────┘         │
│                                         │
│     Centered, max-w-md, bg gradient     │
└─────────────────────────────────────────┘
```

### 2.2 Dashboard Layout (Main App)
```
┌──────────┬──────────────────────────────┐
│          │  Navbar (Notif, User Menu)   │
│          ├──────────────────────────────┤
│ Sidebar  │                              │
│          │   Page Content Area          │
│ - Logo   │                              │
│ - Nav    │   ┌────┐ ┌────┐ ┌────┐      │
│   Items  │   │Stat│ │Stat│ │Stat│      │
│ - User   │   └────┘ └────┘ └────┘      │
│   Info   │                              │
│          │   ┌──────────────────┐       │
│          │   │   Chart Area     │       │
│          │   └──────────────────┘       │
│          │                              │
│          │   ┌──────────────────┐       │
│          │   │   Table / List   │       │
│          │   └──────────────────┘       │
│          │                              │
└──────────┴──────────────────────────────┘
```

## 3. Page Specifications

### 3.1 Login Page
- Centered card layout, gradient background (blue)
- Logo + app name at top
- Fields: Email, Password
- "Masuk" button (primary, full width)
- "Belum punya akun? Daftar di sini" link
- Validation errors inline below fields
- Rate limit error message if exceeded

### 3.2 Register Page
- Centered card layout, same gradient
- Fields: Nama, Email, No. Telepon, Nama Perusahaan, Alamat Perusahaan, Password, Konfirmasi Password
- "Daftar" button
- "Sudah punya akun? Masuk" link

### 3.3 Dashboard Pemohon
```
┌─ Stat Cards (4 cards in grid) ──────────────────┐
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│ │ Total    │ │ Menunggu │ │ Disetujui│ │ Perlu    │ │
│ │ Project  │ │ Review   │ │          │ │ Revisi   │ │
│ │    25    │ │    5     │ │    10    │ │    3     │ │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘ │
└──────────────────────────────────────────────────┘

┌─ Charts Row (2 column) ────────────────────────┐
│ ┌─────────────────────┐ ┌─────────────────────┐ │
│ │ Bar Chart           │ │ Line Chart          │ │
│ │ Status Distribution │ │ Monthly Submissions │ │
│ │ (by status count)   │ │ (trend 12 bulan)    │ │
│ └─────────────────────┘ └─────────────────────┘ │
└──────────────────────────────────────────────────┘

┌─ Recent Projects Table ─────────────────────────┐
│ Project Code │ Title      │ Status    │ Date     │
│ PRJ-2025-001 │ AMDAL PT.. │ ●Submitted│ 01/01/25 │
│ PRJ-2025-002 │ IMB PT...  │ ●Approved │ 02/01/25 │
│ ...          │ ...        │ ...       │ ...      │
│                      [Lihat Semua →]             │
└──────────────────────────────────────────────────┘
```

### 3.4 Dashboard Penilai
```
┌─ Stat Cards (5 cards in grid) ──────────────────────────────┐
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│ │ Total    │ │ Menunggu │ │ Sedang   │ │ Disetujui│ │ Ditolak  │ │
│ │ Pengajuan│ │ Review   │ │ Review   │ │          │ │          │ │
│ │   500    │ │   50     │ │   20     │ │   300    │ │   50     │ │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘ │
└──────────────────────────────────────────────────────────────┘

┌─ Charts Row (3 column) ────────────────────────────────────┐
│ ┌───────────────┐ ┌───────────────┐ ┌───────────────┐      │
│ │ Donut Chart   │ │ Line Chart    │ │ Bar Chart     │      │
│ │ Approval Rate │ │ Monthly Trends│ │ By Category   │      │
│ │ (pie/donut)   │ │ (multi-line)  │ │ (horizontal)  │      │
│ └───────────────┘ └───────────────┘ └───────────────┘      │
└────────────────────────────────────────────────────────────┘

┌─ Recent Reviews Table ──────────────────────────────────────┐
│ Project Code │ Pemohon    │ Keputusan │ Tanggal │ Catatan   │
│ PRJ-2025-001 │ PT ABC     │ ●Approved │ 01/01   │ Sesuai... │
│ PRJ-2025-003 │ PT XYZ     │ ●Revised  │ 02/01   │ Perlu...  │
│ ...                                [Lihat Semua →]          │
└─────────────────────────────────────────────────────────────┘
```

### 3.5 Project List Page (Pemohon)
```
┌─ Header ────────────────────────────────────────┐
│ Permohonan Dokumen                [+ Buat Baru] │
└──────────────────────────────────────────────────┘

┌─ Filters Bar ───────────────────────────────────┐
│ [Search...        ] [Status ▼] [Kategori ▼]     │
│ [Dari Tanggal    ] [Sampai Tanggal   ] [Filter] │
└──────────────────────────────────────────────────┘

┌─ Table ─────────────────────────────────────────┐
│ # │ Kode Project  │ Judul  │ Kategori │ Status  │
│   │               │        │          │         │
│ 1 │ PRJ-2025-0001 │ AMDAL  │ AMDAL    │ ●Draft  │
│   │               │ PT ABC │          │         │
│───┼───────────────┼────────┼──────────┼─────────│
│ 2 │ PRJ-2025-0002 │ IMB    │ IMB      │●Submitted│
│   │               │ Kantor │          │         │
│───┼───────────────┼────────┼──────────┼─────────│
│                                                  │
│ [◄ Prev] Page 1 of 10 [Next ►]   Showing 1-15   │
└──────────────────────────────────────────────────┘

Row Actions (on hover / dropdown):
  - Lihat Detail (all status)
  - Edit (draft, revised only)
  - Submit (draft, revised + has documents)
  - Hapus (draft only)
```

### 3.6 Submission List Page (Penilai)
```
┌─ Header ────────────────────────────────────────┐
│ Daftar Pengajuan Permohonan                      │
└──────────────────────────────────────────────────┘

┌─ Filters ───────────────────────────────────────┐
│ [Search...    ] [Status ▼] [Kategori ▼]          │
│ [Dari] [Sampai] [Export Excel ↓] [Export PDF ↓]  │
└──────────────────────────────────────────────────┘

┌─ Table ─────────────────────────────────────────┐
│ # │ Kode    │ Judul   │ Pemohon   │ Status │ Tgl│
│ 1 │ PRJ-001 │ AMDAL.. │ PT ABC    │●Submit │ .. │
│ 2 │ PRJ-002 │ IMB ... │ PT XYZ    │●Review │ .. │
│───┼─────────┼─────────┼───────────┼────────┼────│
│                                                  │
│ [◄ Prev] Page 1 of 50 [Next ►]                  │
└──────────────────────────────────────────────────┘

Row Actions:
  - Lihat Detail (all)
  - Ambil Review (submitted only)
  - Review (in_review, if current reviewer)
```

### 3.7 Project Create/Edit Form
```
┌─ Header ────────────────────────────────────────┐
│ ← Kembali     Buat Permohonan Baru              │
└──────────────────────────────────────────────────┘

┌─ Form Card ─────────────────────────────────────┐
│                                                  │
│ Judul Permohonan *                               │
│ ┌──────────────────────────────────────────────┐ │
│ │                                              │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ Kategori Dokumen *            Prioritas          │
│ ┌──────────────────────┐  ┌──────────────────┐   │
│ │ Pilih Kategori    ▼  │  │ Normal        ▼  │   │
│ └──────────────────────┘  └──────────────────┘   │
│                                                  │
│ Deskripsi                                        │
│ ┌──────────────────────────────────────────────┐ │
│ │                                              │ │
│ │                                              │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ Catatan Tambahan                                 │
│ ┌──────────────────────────────────────────────┐ │
│ │                                              │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ Dokumen Pendukung                                │
│ ┌──────────────────────────────────────────────┐ │
│ │  ┌─────────────────────────────────────────┐ │ │
│ │  │  📎 Drag & drop file atau klik Browse   │ │ │
│ │  │  PDF, DOC, DOCX, JPG, PNG (max 10MB)   │ │ │
│ │  └─────────────────────────────────────────┘ │ │
│ │                                              │ │
│ │  📄 dokumen_amdal.pdf     2.3 MB  [× Hapus]│ │
│ │  📄 surat_pernyataan.pdf  1.1 MB  [× Hapus]│ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│              [Simpan Draft]  [Submit Permohonan] │
│                                                  │
└──────────────────────────────────────────────────┘
```

### 3.8 Project Detail Page
```
┌─ Header ────────────────────────────────────────┐
│ ← Kembali    PRJ-2025-00001       ●Status Badge │
│                                                  │
│ [Edit] [Submit] [Export PDF]  ← conditional      │
└──────────────────────────────────────────────────┘

┌─ Tabs ──────────────────────────────────────────┐
│ [Informasi] [Dokumen (3)] [Riwayat Penilaian]   │
└──────────────────────────────────────────────────┘

─── Tab: Informasi ───
┌─ Info Card ─────────────────────────────────────┐
│ Judul:        AMDAL PT ABC Indonesia            │
│ Kategori:     AMDAL                             │
│ Prioritas:    ●Normal                           │
│ Pemohon:      John Doe (PT ABC Indonesia)       │
│ Diajukan:     01 Januari 2025, 10:00            │
│ Revisi ke:    0                                 │
│ Deskripsi:    Lorem ipsum dolor sit amet...     │
│ Catatan:      -                                 │
└──────────────────────────────────────────────────┘

─── Tab: Dokumen ───
┌─ Document List ─────────────────────────────────┐
│ 📄 dokumen_amdal.pdf      2.3 MB  v1  [↓ Download] │
│ 📄 surat_pernyataan.pdf   1.1 MB  v1  [↓ Download] │
│ 🖼 foto_lokasi.jpg        800 KB  v1  [↓ Download] │
│                                                  │
│ (if draft/revised): [+ Upload Dokumen Baru]      │
└──────────────────────────────────────────────────┘

─── Tab: Riwayat Penilaian ───
┌─ Timeline ──────────────────────────────────────┐
│                                                  │
│ ● 01 Jan 2025, 10:00 — Project Dibuat            │
│ │ Status: Draft                                  │
│ │                                                │
│ ● 02 Jan 2025, 14:00 — Dikirim untuk Penilaian  │
│ │ Status: Draft → Submitted                      │
│ │                                                │
│ ● 03 Jan 2025, 09:30 — Review oleh Budi Penilai │
│ │ Status: Submitted → In Review                  │
│ │                                                │
│ ● 03 Jan 2025, 15:00 — Diminta Revisi           │
│ │ Status: In Review → Revised                    │
│ │ Catatan: "Dokumen AMDAL perlu dilengkapi..."   │
│ │                                                │
│ ● 04 Jan 2025, 11:00 — Dikirim Ulang            │
│   Status: Revised → Submitted                    │
│                                                  │
└──────────────────────────────────────────────────┘
```

### 3.9 Review Page (Penilai)
```
┌─ Header ────────────────────────────────────────┐
│ ← Kembali    Review: PRJ-2025-00001  ●In Review │
└──────────────────────────────────────────────────┘

┌─ Project Info Summary (readonly) ───────────────┐
│ Judul: AMDAL PT ABC     Pemohon: PT ABC         │
│ Kategori: AMDAL          Prioritas: Normal      │
│ Deskripsi: Lorem ipsum...                       │
│ Revisi ke: 1                                    │
└──────────────────────────────────────────────────┘

┌─ Dokumen ───────────────────────────────────────┐
│ 📄 dokumen_amdal_v2.pdf   2.5 MB [↓ Download]  │
│ 📄 surat_pernyataan.pdf   1.1 MB [↓ Download]  │
└──────────────────────────────────────────────────┘

┌─ Review History (jika ada) ─────────────────────┐
│ Revisi 1 — 03 Jan 2025 oleh Budi                │
│ "Dokumen AMDAL perlu dilengkapi dengan..."      │
└──────────────────────────────────────────────────┘

┌─ Form Penilaian ────────────────────────────────┐
│                                                  │
│ Catatan Penilaian                                │
│ ┌──────────────────────────────────────────────┐ │
│ │                                              │ │
│ │                                              │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ [🟢 Setujui]  [🟡 Minta Revisi]  [🔴 Tolak]    │
│                                                  │
└──────────────────────────────────────────────────┘

Confirmation Modal (on any action):
┌─────────────────────────────────────────┐
│ ⚠️ Konfirmasi                           │
│                                         │
│ Apakah Anda yakin ingin [menyetujui/    │
│ meminta revisi/menolak] permohonan ini? │
│                                         │
│ Catatan: "..."                          │
│                                         │
│         [Batal]  [Ya, Lanjutkan]        │
└─────────────────────────────────────────┘
```

### 3.10 Notification Panel
```
┌─ Navbar notification bell ──┐
│  🔔 (3)                     │ ← Badge with unread count
└─────────────────────────────┘

Dropdown / Panel:
┌─────────────────────────────────────────┐
│ Notifikasi              [Tandai Semua]  │
├─────────────────────────────────────────┤
│ 🔵 Permohonan Disetujui ✓              │
│ PRJ-2025-00001 telah disetujui          │
│ 2 menit yang lalu                       │
├─────────────────────────────────────────┤
│ 🔵 Permohonan Perlu Revisi              │
│ PRJ-2025-00003 memerlukan revisi        │
│ 1 jam yang lalu                         │
├─────────────────────────────────────────┤
│ ○ Permohonan Baru                       │
│ PT XYZ mengajukan permohonan baru       │
│ 3 jam yang lalu                         │
├─────────────────────────────────────────┤
│         [Lihat Semua Notifikasi]        │
└─────────────────────────────────────────┘
```

## 4. Component Library

### Reusable Components
| Component       | Props                              | Usage                    |
|-----------------|------------------------------------|--------------------------|
| StatusBadge     | status, size                       | Show status with color   |
| DataTable       | columns, data, loading, sortable   | Sortable data table      |
| Pagination      | meta, onPageChange                 | Page navigation          |
| FileUpload      | accept, maxSize, multiple          | Drag & drop upload       |
| ConfirmDialog   | title, message, type, onConfirm    | Confirm destructive acts |
| StatCard        | title, value, icon, color, trend   | Dashboard stat card      |
| EmptyState      | icon, title, description, action   | No data placeholder      |
| LoadingSpinner  | size, text                         | Loading indicator        |
| Toast           | message, type, duration            | Notification toast       |
| NotificationBell| count                              | Navbar notification      |
| SearchInput     | modelValue, placeholder, debounce  | Debounced search         |
| DateRangePicker | from, to                           | Date range filter        |

## 5. Responsive Considerations
- Sidebar collapsible on mobile (hamburger menu)
- Tables become card-based on mobile
- Stat cards stack vertically on small screens
- Charts resize responsively
- Modals become full-screen on mobile
- Breakpoints: sm:640px, md:768px, lg:1024px, xl:1280px
