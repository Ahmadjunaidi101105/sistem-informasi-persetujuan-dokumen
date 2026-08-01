# Fix Plan — SIPDOK Final Polish (FIX-1 s/d FIX-20)

> Disusun berdasarkan audit kode aktual (bukan asumsi dari docs lama).
> Status verifikasi saat dokumen ini dibuat: working tree bersih, commit terakhir `e5c12c8`.
> Tujuan: proyek lulus kriteria technical test 100% — clean, rapi, tanpa bug, UI menarik & konsisten tema **hijau tua – oranye – hitam** (terinspirasi identitas visual instansi lingkungan hidup, TANPA menyebut/menduplikasi nama atau logo instansi tersebut).

Legenda prioritas: **P0 CRITICAL** (blocker kelulusan/fungsional) → **P1 HIGH** (kualitas & nilai poin besar) → **P2 MEDIUM** (polish/dokumentasi).

---

## FIX-1 — API Response Format Consistency (P0)
**Masalah:** Riwayat commit menunjukkan format response sempat tidak konsisten (`res.data.data` bug). Perlu audit ulang menyeluruh, bukan cuma di titik yang sudah di-fix.
**Scope:**
- Audit `backend/app/Traits`/`ApiResponse` helper — pastikan SEMUA controller (`AuthController`, `ProjectController`, `ReviewController`, `DashboardController`, `DocumentController`, `NotificationController`, `ExportController`, `DocumentCategoryController`) memakai satu format: `{ success, message, data, meta? }`.
- Pastikan endpoint list (paginated) selalu mengembalikan `meta` (current_page, per_page, total, last_page) dengan key yang sama persis di semua endpoint.
- Pastikan error response (422, 403, 404, 401, 429, 500) juga lewat wrapper yang sama (`success: false, message, errors?`).
**Acceptance:** Bandingkan payload semua endpoint GET-list dan GET-detail — strukturnya identik pada level luar.

## FIX-2 — Frontend API Parsing Consistency (P0)
**Masalah:** `src/api/client.js` sudah unwrap `.data` di response interceptor — pastikan TIDAK ADA lagi pemanggilan `response.data.data.data` atau akses ganda di store/composable manapun.
**Scope:** Grep seluruh `frontend/src` untuk pola `.data.data`, `response.data.data`, cek tiap store (`stores/*.js`) dan halaman yang fetch langsung tanpa store.
**Acceptance:** Semua list page (`ProjectListPage`, `SubmissionListPage`, `ReviewHistoryPage`, `NotificationsPage`) menampilkan data dan pagination dengan benar tanpa `undefined`.

## FIX-3 — Auth Flow Cleanup (P0)
**Masalah:** Auth backend memakai Sanctum **token** (`createToken()->plainTextToken`, Bearer di header), tapi `stores/auth.js` masih memanggil `getCsrfCookie()` (`/sanctum/csrf-cookie`) sebelum login — sisa pendekatan cookie-SPA yang tidak relevan lagi dan membingungkan.
**Scope:**
- Hapus panggilan `getCsrfCookie()` yang tidak perlu dari flow login (atau pastikan backend memang expect cookie flow — pilih SATU pendekatan, jangan campur).
- Pastikan token disimpan aman (localStorage sudah dipakai — pastikan dibersihkan saat logout & saat 401).
- Pastikan `router/index.js` guard (`checkAuth`) tidak menyebabkan flicker/redirect loop saat refresh halaman (sudah pernah jadi bug — pastikan benar-benar fixed dengan test manual reload di semua role).
- Cek `SANCTUM_STATEFUL_DOMAINS` di `.env` — hapus jika token-based auth murni tidak butuh statefulness cookie.
**Acceptance:** Login, refresh page (F5) di halaman dashboard pemohon & penilai, logout — semua mulus tanpa flicker/redirect ganda/401 tak terduga.

## FIX-4 — Landing Page Baru (P1)
**Masalah:** Saat ini route `/` langsung `redirect: '/login'` — TIDAK ADA landing page sama sekali.
**Scope:**
- Buat `frontend/src/pages/LandingPage.vue` (public, tanpa auth).
- Konten minimal: Hero section (judul aplikasi SIPDOK + tagline singkat + CTA "Masuk" / "Daftar sebagai Pemohon"), section ringkas alur (Ajukan → Review → Disetujui), footer sederhana.
- Update `router/index.js`: `{ path: '/', name: 'landing', component: LandingPage, meta: { guest: true } }` — hapus redirect paksa. Guest guard tetap mengalihkan user yang sudah login ke dashboard masing-masing.
- Gunakan tema warna hijau-oranye-hitam (lihat FIX-5).
**Acceptance:** Buka `/` tanpa login → landing page tampil profesional; user login tetap diarahkan ke dashboard.

## FIX-5 — Theme Overhaul: Hijau–Oranye–Hitam (P1)
**Masalah:** `main.css` cuma `@import "tailwindcss"` polos — tidak ada tema kustom sama sekali. Semua warna default Tailwind dipakai ad-hoc.
**Scope:** Tailwind v4 pakai CSS-based theme (`@theme` di `main.css`), bukan `tailwind.config.js`. Tambahkan token warna:
```css
@theme {
  --color-brand-green-50: #E8F5EE;
  --color-brand-green-100: #C6E8D6;
  --color-brand-green-500: #1F9D55;
  --color-brand-green-600: #16803D;
  --color-brand-green-700: #0F6B3B;
  --color-brand-green-900: #0A4028;

  --color-brand-orange-50: #FFF4E5;
  --color-brand-orange-100: #FFE1B3;
  --color-brand-orange-500: #F7941D;
  --color-brand-orange-600: #E17F0A;

  --color-brand-dark: #16181A;
}
```
- Terapkan konsisten: sidebar/navbar gelap (near-black) dengan aksen hijau, tombol primary = hijau, tombol/badge highlight & warning = oranye, status badge disesuaikan (approved=hijau, revised=oranye, rejected=merah tetap standar, in_review=biru/netral).
- Ganti semua warna hardcoded (`bg-blue-600`, `bg-indigo-500`, dst di komponen umum) ke token brand secara konsisten — cek `components/common/*`, `layouts/*`, `pages/auth/*`.
**Acceptance:** Seluruh app terasa satu sistem visual, bukan campuran warna default Tailwind.

## FIX-6 — UI Polish Komponen (P1)
**Scope:**
- `DataTable.vue`, `Pagination.vue`, `StatusBadge.vue`, `EmptyState.vue`, `Toast.vue`, `ConfirmDialog.vue`, `LoadingSpinner.vue` — audit visual: spacing konsisten, rounded-corner konsisten, shadow konsisten, hover/focus states jelas, kontras teks cukup (a11y).
- Sidebar & Navbar: pastikan aktif-state jelas, responsive collapse rapi.
- Form pages (`ProjectCreatePage`, `ProjectEditPage`, `ReviewPage`): validasi error tampil jelas per-field, bukan cuma toast generik.
**Acceptance:** Review visual tiap halaman utama, tidak ada elemen terpotong/misaligned/inconsistent spacing.

## FIX-7 — Loading Lambat / Data Fetching Fix (P0)
**Masalah:** User melaporkan banyak loading lama.
**Scope:**
- Cek apakah dashboard/list memanggil banyak request berurutan (waterfall) yang bisa di-parallel-kan (`Promise.all`).
- Cek apakah `checkAuth()` di router guard dipanggil berulang tiap navigasi (sudah ada flag `authChecked`, verifikasi masih efektif).
- Cek backend: apakah endpoint list/dashboard lambat karena N+1 (lihat FIX-8) atau cache tidak kena (Redis TTL dashboard 5 menit, document_categories 1 jam — pastikan cache HIT bekerja, cek `DashboardService` & Observer invalidation tidak invalidate berlebihan).
- Tambahkan skeleton loading / spinner yang jelas di semua halaman dengan fetch data, supaya minimal *terasa* responsif meski data besar (10.000 project di seeder).
**Acceptance:** Ukur response time list & dashboard API (target dari docs: list <200ms, detail <100ms) — kalau tidak tercapai, identifikasi query lambat dengan `EXPLAIN ANALYZE`.

## FIX-8 — Eager Loading / N+1 Query Fix (P1)
**Scope:**
- Audit ulang semua query di `ProjectService`, `ReviewService`, `DashboardService`, dan controller yang query langsung — pastikan `with()`/`withCount()` dipakai untuk relasi yang ditampilkan di Resource (user, documentCategory, currentReviewer, documents, reviews).
- Aktifkan `Model::preventLazyLoading()` di `AppServiceProvider` (env local/testing) sementara untuk mendeteksi N+1 yang lolos audit manual, lalu jalankan seluruh flow aplikasi dan perbaiki yang ketahuan.
**Acceptance:** Tidak ada `LazyLoadingViolationException` saat menjalankan seluruh user flow di local dengan lazy-loading prevention aktif.

## FIX-9 — File Upload/Download Fix (P1)
**Scope:**
- Verifikasi upload dokumen (`POST /projects/{id}/documents`) — validasi mime (pdf/doc/docx/jpg/jpeg/png), max 10MB, max 10 dokumen per project, UUID filename — semua sesuai `BR-003` di `05-FLOW-AND-RULES.md`.
- Verifikasi `GET /documents/{id}/download` — authorization benar (pemilik project atau penilai), file benar-benar ke-download bukan redirect broken.
- Frontend: `FileUpload.vue` (cek apakah sudah ada — di progress tracker masih unchecked) — progress indicator saat upload, preview nama file, tombol hapus sebelum submit.
**Acceptance:** Upload multi-file, lihat di detail project, download tiap file berhasil, hapus dokumen berhasil (sesuai rule status draft/revised saja).

## FIX-10 — Notifications: Bell + List + Polling (P2)
**Scope:**
- `NotificationBell.vue` di navbar: badge unread count, dropdown daftar terbaru.
- `NotificationsPage.vue`: list lengkap, mark as read (single & all), pagination.
- Polling interval wajar (misal 30-60s) via `setInterval` di store, bersihkan interval saat unmount/logout — pastikan TIDAK ada memory leak / multiple interval menumpuk.
**Acceptance:** Trigger aksi (submit/review/approve/revise/reject) di satu sisi role, notifikasi muncul di sisi lain dalam waktu wajar tanpa refresh manual.

## FIX-11 — Export: Excel + PDF Download (P2)
**Scope:**
- `GET /export/projects` (Excel, Maatwebsite) — filter sama seperti list API, rate limit 5/menit.
- `GET /export/projects/{id}/pdf` (dompdf) — detail project + dokumen + histori review.
- Frontend: tombol export di `ProjectListPage`/`SubmissionListPage`, trigger file download langsung (blob response, bukan buka tab kosong).
**Acceptance:** File Excel/PDF ter-download, isinya benar dan terbaca, rate limit teruji (request ke-6 dalam 1 menit → 429 dengan pesan jelas).

## FIX-12 — Workflow Actions Correctness (P0)
**Scope:** Re-verifikasi end-to-end semua transisi status sesuai `BR-002`:
- Submit (draft/revised → submitted), Take Review (submitted → in_review, set `current_reviewer_id`), Approve/Revise/Reject (in_review → final/revised, catat `notes` min 10 karakter untuk revise/reject).
- Pastikan transisi ILEGAL benar-benar diblok backend (403/422), bukan cuma disembunyikan di UI (tombol hidden tidak cukup — request langsung ke API harus tetap ditolak).
- Pastikan `revision_count` bertambah tiap revisi, `current_reviewer_id` di-reset null saat revised.
**Acceptance:** Test manual + otomatis (lihat FIX-17) untuk tiap transisi valid & invalid.

## FIX-13 — Dashboard Charts (ApexCharts) (P1)
**Masalah:** Progress tracker menandai chart items sebagai belum lengkap; `StatusChart.vue`/`TrendChart.vue` sudah disentuh oleh fix commit terakhir tapi perlu verifikasi data benar-benar match struktur `DashboardService`.
**Scope:**
- Pemohon: bar chart distribusi status, line chart pengajuan per bulan.
- Penilai: donut chart approval rate, multi-line chart tren bulanan per keputusan, horizontal bar per kategori dokumen.
- Pastikan chart pakai palet brand (hijau/oranye) bukan warna default ApexCharts.
- Pastikan chart handle empty-state (user baru tanpa data) tanpa error.
**Acceptance:** Login sebagai pemohon & penilai dengan data seed — semua chart render benar, warna konsisten tema, tidak ada console error.

## FIX-14 — Item Progress Tracker yang Belum Tercentang (P1)
Cross-check ulang `docs/07-PROGRESS.md` — item berikut tercatat unchecked, verifikasi & selesaikan:
- `DashboardResource` (API Resource khusus dashboard, saat ini mungkin masih array manual)
- Middleware role (dokumentasi lama minta `EnsureRole` — sudah dipastikan pendekatan aktual pakai Policy/Gate; putuskan: dokumentasikan ulang keputusan ini atau tambahkan middleware tipis untuk konsistensi route-level, sesuai gaya proyek)
- Rate limiting config (verifikasi eksplisit di `RouteServiceProvider`/`bootstrap/app.php`: 5/min auth, 60/min general, 5/min export)
- `FileUpload.vue`, composables (`useAuth`, `useProjects`, `useNotifications`, `usePagination`, `useToast`) — cek apakah sudah ada file-nya atau logic-nya masih inline di store/page.
**Acceptance:** Update `07-PROGRESS.md` mencerminkan kondisi nyata setelah tiap item diverifikasi/diselesaikan.

## FIX-15 — Error Handling & Pesan Bahasa Indonesia (P2)
**Scope:**
- Semua pesan error user-facing (toast, validasi form) dalam Bahasa Indonesia yang jelas, bukan pesan Laravel default berbahasa Inggris.
- 404/403/500 punya halaman/state yang jelas, bukan blank screen.
- Custom exception handler (`bootstrap/app.php` withExceptions) mengembalikan format `ApiResponse` konsisten untuk semua exception type (ValidationException, ModelNotFoundException, AuthorizationException, ThrottleRequestsException).
**Acceptance:** Trigger tiap jenis error dari frontend, pesan yang muncul jelas dan dalam Bahasa Indonesia.

## FIX-16 — Responsive Design (P2)
**Scope:** Verifikasi ulang (progress tracker sudah tandai selesai, tapi re-check karena banyak perubahan setelahnya) — mobile (375px), tablet (768px), desktop. Sidebar collapse, table jadi card/scroll horizontal di mobile, form tidak overflow.
**Acceptance:** Test manual di 3 breakpoint untuk halaman: landing, login, dashboard, list, detail, review form.

## FIX-17 — Test Suite Lengkap (P1)
**Masalah:** Test yang ada baru: `AuthenticationTest`, `ProjectCRUDTest`, `ProjectWorkflowTest`, `ProjectStatusTest` (Unit), `ProjectTest` (Unit), `ProjectPolicyTest`. **Belum ada** sama sekali: `DocumentUploadTest`, `DashboardTest`, `NotificationTest`, `ExportTest`.
**Scope:** Tulis 4 test file yang hilang, mencakup happy path + rule violation (mis. upload >10MB ditolak, export rate limit, dashboard cache, notification mark-as-read authorization). Jalankan `php artisan test` — semua PASS, tidak ada skipped tanpa alasan.
**Acceptance:** `php artisan test` hijau semua, coverage mencakup semua business rule di `05-FLOW-AND-RULES.md`.

## FIX-18 — Git Cleanup (P2)
**Scope:** Review branch lokal/remote — hapus feature branch yang sudah merge dan tidak dipakai lagi (konfirmasi ke user dulu sebelum delete, karena ini aksi destructive). Tag `v1.0.0` sudah ada? kalau belum, buat tag setelah semua fix selesai. Commit message tetap ikuti convention (`type: description`).
**Acceptance:** `git log --graph` bersih, tidak ada branch basi, tag rilis final ada.

## FIX-19 — E2E Manual Test Checklist Penuh (P0)
Jalankan skenario penuh sebelum submit:
1. Register pemohon baru → login → landing page redirect benar.
2. Buat project draft → upload dokumen → submit → cek notifikasi ke penilai.
3. Login penilai → lihat submission list → take review → cek notifikasi ke pemohon.
4. Approve satu project, Revise satu project (cek notes wajib), Reject satu project (cek notes wajib).
5. Pemohon terima notifikasi tiap event, buka project revised, submit ulang → `revision_count` naik.
6. Cek dashboard kedua role — angka & chart match data aktual.
7. Export Excel & PDF berhasil.
8. Refresh page (F5) di berbagai halaman berlogin — tidak ada flicker/redirect aneh.
9. Logout → coba akses halaman protected via URL langsung → redirect ke login.
10. `docker-compose up` dari kondisi bersih (`docker-compose down -v`) → semua service up, migrate+seed jalan, app bisa diakses end-to-end.
**Acceptance:** Semua 10 skenario di atas lulus tanpa error console/network.

## FIX-20 — Dokumentasi Final (P2)
**Scope:** Update `README.md` (screenshot UI baru), `docs/07-PROGRESS.md` (status akurat), `docs/API-DOCUMENTATION.md` (endpoint yang berubah), tambahkan catatan singkat di README tentang keputusan desain tema warna (tanpa menyebut instansi manapun).
**Acceptance:** Reviewer eksternal bisa `docker-compose up`, ikuti README, dan menjalankan aplikasi tanpa instruksi tambahan.

---

## Urutan Eksekusi yang Disarankan
1. **P0 dulu semua** (FIX-1, 2, 3, 7, 12) — pastikan fondasi fungsional benar-benar solid.
2. **P1** (FIX-4, 5, 6, 8, 9, 13, 14, 17) — nilai poin terbesar & tampilan.
3. **P2** (FIX-10, 11, 15, 16, 18, 20) — polish & dokumentasi.
4. **FIX-19 (E2E)** dijalankan ulang di akhir sebagai gerbang kelulusan final.

Catatan: tema warna TIDAK meniru identitas resmi instansi manapun — hanya terinspirasi palet hijau-oranye-hitam sebagai pilihan desain umum, sesuai arahan agar tidak menyebut nama instansi secara eksplisit.
