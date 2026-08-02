# Panduan Demo & Pengujian SIPDOK

Panduan langkah demi langkah untuk menjalankan dan mendemokan SIPDOK dari nol
sampai seluruh alur bisnis teruji.

Estimasi waktu: **±10 menit** untuk menjalankan, **±20 menit** untuk demo penuh.

---

## Bagian 0 — Prasyarat

| Kebutuhan | Versi | Cek dengan |
|-----------|-------|------------|
| Docker Desktop | 4.x, sedang berjalan | `docker --version` |
| Node.js | 20+ | `node --version` |
| Git | any | `git --version` |

Port yang harus bebas: **8080** (API), **5173** (frontend), **5432** (PostgreSQL), **6379** (Redis).

Cek port yang sedang dipakai:
```bash
netstat -ano | findstr "8080 5173 5432 6379"
```

---

## Bagian 1 — Menjalankan Backend

### 1.1 Masuk ke folder proyek
```bash
cd c:\sistem-informasi-persetujuan-dokumen\sipdok
```

### 1.2 Nyalakan seluruh service Docker
```bash
docker compose up -d
```

Tunggu ±30 detik, lalu pastikan **5 container** berstatus `Up`:
```bash
docker compose ps
```

Yang harus muncul: `sipdok-nginx`, `sipdok-php`, `sipdok-queue`, `sipdok-postgres`, `sipdok-redis`.

> `sipdok-queue` adalah worker antrean yang memproses notifikasi. Kalau container
> ini mati, notifikasi tidak akan terkirim.

### 1.3 Siapkan aplikasi (hanya untuk pertama kali)

```bash
docker compose exec php composer install
docker compose exec php php artisan key:generate
```

### 1.4 Isi database dengan data contoh

> ⚠️ Perintah ini **menghapus seluruh isi database** lalu mengisinya ulang.
> Lewati jika data Anda sudah ada dan ingin dipertahankan.

```bash
docker compose exec php php artisan migrate:fresh --seed
```

Proses ini butuh ±40 detik dan menghasilkan:
- 2.000 akun (1.000 Pemohon + 1.000 Penilai)
- 10.000 permohonan dengan sebaran status realistis
- 10 kategori dokumen

Anda akan melihat log seperti:
```
Seeding 999 pemohon...
Seeding 999 penilai...
Seeding 10,000 projects with documents and reviews...
Successfully seeded 10000 projects.
```

### 1.5 Aktifkan optimasi performa (WAJIB)

```bash
make optimize
```

Atau tanpa `make`:
```bash
docker compose exec php php artisan config:cache
docker compose exec php php artisan route:cache
```

> **Kenapa wajib?** Source code di-*bind mount* dari Windows ke container.
> Tanpa langkah ini setiap request memakan ±5 detik; dengan langkah ini
> turun menjadi ±0,25 detik.

### 1.6 Pastikan backend hidup

```bash
curl http://localhost:8080/up
```
Harus membalas `200`.

Uji satu endpoint API:
```bash
curl -X POST http://localhost:8080/api/v1/auth/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"pemohon@sipdok.test\",\"password\":\"password\"}"
```
Harus membalas `{"success":true,"message":"Login berhasil", ...}` beserta token.

---

## Bagian 2 — Menjalankan Frontend

Buka **terminal baru** (biarkan terminal pertama tetap terbuka).

```bash
cd c:\sistem-informasi-persetujuan-dokumen\sipdok\frontend
npm install      # hanya perlu sekali
npm run dev
```

Akan muncul:
```
➜  Local:   http://localhost:5173/
```

Buka **http://localhost:5173/** di browser.

> Frontend meneruskan permintaan `/api` ke `http://localhost:8080` melalui proxy
> Vite, sehingga tidak ada masalah CORS.

---

## Bagian 3 — Akun untuk Demo

| Peran | Email | Password |
|-------|-------|----------|
| Pemohon | `pemohon@sipdok.test` | `password` |
| Penilai | `penilai@sipdok.test` | `password` |

> Untuk demo alur dua peran, buka **Penilai di jendela Incognito/Private** agar
> kedua sesi bisa berjalan bersamaan tanpa saling menimpa token.

---

## Bagian 4 — Skenario Demo Lengkap

### Langkah 1 — Landing Page

1. Buka `http://localhost:5173/`
2. Anda akan melihat halaman publik dengan tema hijau–oranye–hitam.

**Yang perlu ditunjukkan:**
- Hero dengan kartu contoh permohonan beserta linimasa statusnya
- Klik menu **Fitur**, **Alur Proses**, **Untuk Siapa**, **FAQ** — halaman
  akan bergulir mulus ke bagian terkait
- Buka FAQ, klik salah satu pertanyaan untuk membuka jawabannya
- Perkecil jendela browser → menu berubah menjadi tombol hamburger

---

### Langkah 2 — Registrasi Pemohon Baru

1. Klik **Daftar** di kanan atas
2. Isi formulir:

| Kolom | Contoh isian | Keterangan |
|-------|--------------|------------|
| Nama | `Budi Santoso` | wajib |
| Email | `budi@contoh.com` | wajib, harus unik |
| Password | `password123` | wajib, minimal 8 karakter |
| Konfirmasi Password | `password123` | harus sama |
| Telepon | `081234567890` | opsional |
| Nama Perusahaan | `PT Maju Bersama` | **wajib** |
| Alamat Perusahaan | `Jl. Merdeka No. 10, Jakarta` | **wajib** |

3. Klik **Daftar**

**Uji validasi (opsional, bagus untuk demo):**
- Kosongkan Nama Perusahaan → muncul pesan galat di bawah kolom tersebut
- Isi password kurang dari 8 karakter → muncul galat panjang minimal
- Daftar ulang dengan email `pemohon@sipdok.test` → galat email sudah terpakai

> Registrasi mandiri hanya tersedia untuk **Pemohon**. Akun Penilai dibuat oleh
> pengelola sistem, sesuai aturan bisnis BR-011.

---

### Langkah 3 — Login sebagai Pemohon

1. Masuk dengan `pemohon@sipdok.test` / `password`
   (akun ini sudah punya data contoh, lebih menarik untuk didemokan)
2. Anda akan diarahkan ke **Dashboard Pemohon**

**Yang perlu ditunjukkan:**
- 4 kartu statistik: Total, Menunggu, Disetujui, Perlu Revisi
- Grafik batang distribusi status
- Grafik garis tren pengajuan bulanan
- Tabel permohonan terbaru

**Uji ketahanan sesi:** tekan **F5**. Halaman harus tetap di dashboard tanpa
berkedip atau terlempar ke halaman login.

---

### Langkah 4 — Membuat Permohonan Baru

1. Menu sisi kiri → **Permohonan** → tombol **Buat Permohonan**
2. Isi formulir:

| Kolom | Contoh isian |
|-------|--------------|
| Judul Permohonan | `Pengajuan Izin Lingkungan Pabrik Tekstil` |
| Kategori Dokumen | `Izin Lingkungan` |
| Prioritas | `Tinggi` |
| Deskripsi | `Permohonan izin lingkungan untuk fasilitas produksi tekstil.` |
| Catatan Tambahan | `Dokumen menyusul apabila diperlukan.` |

3. Pada bagian **Dokumen Pendukung**, unggah 1–2 berkas
   (PDF/DOC/DOCX/JPG/PNG, maksimal 10 MB per berkas)
4. Klik **Simpan Draft**

**Uji validasi berkas (bagus untuk demo):**
- Coba unggah file `.exe` atau `.zip` → ditolak
- Coba unggah file lebih dari 10 MB → ditolak dengan pesan batas ukuran

**Yang perlu ditunjukkan:** kode permohonan otomatis terbentuk dengan format
`PRJ-{TAHUN}-{5 digit}`, misalnya `PRJ-2026-00001`.

---

### Langkah 5 — Mengajukan Permohonan

1. Buka permohonan yang tadi dibuat (status masih **Draft**)
2. Klik **Submit**
3. Konfirmasi pada dialog yang muncul

**Uji aturan bisnis — coba submit tanpa dokumen:**
Buat permohonan baru tanpa melampirkan berkas, lalu tekan Submit. Sistem menolak
dengan pesan:
> *"Lampirkan minimal 1 dokumen sebelum mengajukan permohonan."*

Ini membuktikan aturan divalidasi di sisi server, bukan sekadar disembunyikan di
tampilan.

**Setelah submit berhasil:**
- Status berubah **Draft → Diajukan**
- Tombol Edit menghilang (permohonan terkunci)
- Notifikasi terkirim ke seluruh Penilai

---

### Langkah 6 — Login sebagai Penilai

> Gunakan **jendela Incognito** agar sesi Pemohon tetap aktif.

1. Buka `http://localhost:5173/login` di Incognito
2. Masuk dengan `penilai@sipdok.test` / `password`
3. Anda diarahkan ke **Dashboard Penilai**

**Yang perlu ditunjukkan:**
- 5 kartu statistik untuk seluruh permohonan
- Grafik donat **Tingkat Persetujuan**
- Grafik garis **Tren Keputusan Bulanan** (terpisah per keputusan)
- Grafik batang **Permohonan per Kategori**
- Tabel **Penilaian Terakhir** berisi keputusan yang pernah Anda berikan
- Ikon lonceng di kanan atas menampilkan jumlah notifikasi belum dibaca

> **Catatan penting saat demo.** Permohonan yang baru diajukan **tidak** muncul
> di tabel "Penilaian Terakhir" — tabel itu memang berisi keputusan yang sudah
> Anda buat. Permohonan baru terhitung pada kartu **Menunggu Dinilai** dan
> tampil di menu **Daftar Pengajuan** dengan status *Diajukan*. Ini sesuai
> aturan BR-004: penilai *mengambil* berkas, bukan ditugaskan, sehingga satu
> berkas tidak dinilai dua orang sekaligus.

---

### Langkah 7 — Mengambil Berkas untuk Dinilai

1. Menu sisi kiri → **Daftar Pengajuan**
2. Cari permohonan Anda — gunakan kolom pencarian (ketik judul atau kode)
3. Klik menu titik tiga di kolom Aksi → **Ambil untuk Dinilai**
4. Konfirmasi

**Yang terjadi:**
- Status berubah **Diajukan → Sedang Dinilai**
- Anda tercatat sebagai penilai yang menangani berkas ini
- Notifikasi terkirim ke Pemohon

**Uji aturan penguncian:** satu berkas hanya boleh ditangani satu penilai. Jika
penilai lain mencoba mengambil berkas yang sama, sistem menolak dengan pesan
*"Permohonan ini sudah diambil oleh penilai lain."*

---

### Langkah 8 — Menilai Permohonan

1. Klik menu Aksi → **Nilai** (atau buka dari kartu dashboard)
2. Halaman penilaian terbuka berisi:
   - Informasi pengajuan
   - Daftar dokumen lampiran — klik **Unduh** untuk mengujinya
   - Formulir penilaian
   - Riwayat penilaian di kolom kanan

**Uji validasi catatan:**
Kosongkan catatan lalu klik **Minta Revisi** → ditolak, karena catatan wajib diisi
minimal 10 karakter untuk keputusan Revisi dan Tolak.

3. Isi catatan, misalnya:
   > `Dokumen AMDAL belum dilampirkan. Mohon dilengkapi sebelum diajukan kembali.`
4. Klik **Minta Revisi** → konfirmasi

**Yang terjadi:**
- Status berubah **Sedang Dinilai → Perlu Revisi**
- Penghitung revisi bertambah menjadi 1
- Berkas dilepas dari penilai, sehingga bisa dinilai ulang setelah diperbaiki
- Notifikasi terkirim ke Pemohon

---

### Langkah 9 — Pemohon Memperbaiki dan Mengajukan Ulang

Kembali ke jendela **Pemohon**.

1. Klik ikon lonceng → notifikasi *"Permohonan Perlu Revisi"* muncul
2. Klik notifikasi tersebut → langsung diarahkan ke halaman perbaikan
3. Buka tab **Riwayat Penilaian** untuk membaca catatan dari Penilai
4. Perbaiki data atau unggah dokumen tambahan
5. Klik **Submit Permohonan**

**Yang terjadi:**
- Status berubah **Perlu Revisi → Diajukan**
- Permohonan kembali masuk antrean penilaian

---

### Langkah 10 — Persetujuan Akhir

Kembali ke jendela **Penilai**.

1. **Daftar Pengajuan** → ambil kembali berkas tersebut
2. Buka halaman penilaian
3. Isi catatan, misalnya:
   > `Seluruh dokumen telah lengkap dan memenuhi persyaratan.`
4. Klik **Setujui Dokumen** → konfirmasi

**Yang terjadi:**
- Status berubah **Sedang Dinilai → Disetujui** (status final)
- Waktu persetujuan tercatat
- Notifikasi terkirim ke Pemohon

**Uji status final:** buka kembali permohonan tersebut sebagai Pemohon — tombol
Edit dan Submit sudah tidak tersedia. Permohonan yang sudah disetujui tidak dapat
diubah lagi.

---

### Langkah 11 — Notifikasi

Di jendela mana pun:

1. Klik ikon lonceng → daftar ringkas notifikasi terbaru
2. Klik **Lihat Semua** atau buka menu **Notifikasi**
3. Tandai satu notifikasi sebagai dibaca → penanda biru hilang, penghitung berkurang
4. Klik **Tandai Semua Dibaca** → seluruh notifikasi tertandai

**Yang perlu ditunjukkan:** klik notifikasi mana pun akan membawa Anda langsung ke
permohonan yang bersangkutan.

---

### Langkah 12 — Ekspor Dokumen

**Ekspor Excel (sebagai Penilai):**
1. **Daftar Pengajuan** → klik **Export Excel**
2. Tombol berubah menjadi *"Menyiapkan berkas..."* selama proses berlangsung
3. Berkas `.xlsx` terunduh

> Ekspor seluruh data (9.000 baris) memerlukan ±12 detik. Gunakan penyaring
> status atau kategori terlebih dulu agar jauh lebih cepat (±3 detik).

**Ekspor PDF (sebagai Pemohon):**
1. Buka detail sebuah permohonan
2. Klik **Export PDF**
3. Berkas PDF berisi detail permohonan, dokumen, dan riwayat penilaian akan terunduh

**Uji pembatasan laju:** klik Export Excel lebih dari 5 kali dalam satu menit →
permintaan ke-6 ditolak dengan pesan *"Terlalu banyak permintaan."*

---

### Langkah 13 — Penyaringan, Pencarian, dan Pengurutan

Pada **Daftar Pengajuan** atau **Permohonan**:

| Fitur | Cara mencoba |
|-------|--------------|
| Pencarian | Ketik kode/judul/perusahaan — hasil tersaring otomatis setelah jeda 300 ms |
| Saring status | Pilih `Disetujui` pada dropdown status |
| Saring kategori | Pilih `Izin Lingkungan` |
| Pengurutan | Klik judul kolom untuk membalik urutan naik/turun |
| Penomoran halaman | Pindah ke halaman berikutnya di bagian bawah tabel |

Dengan 10.000 data, setiap perpindahan halaman tetap di bawah 0,3 detik.

---

### Langkah 14 — Pembatasan Akses Antar Peran

Ini poin penting untuk menunjukkan keamanan sistem.

| Pengujian | Hasil yang diharapkan |
|-----------|----------------------|
| Pemohon membuka `/penilai/dashboard` | Dialihkan ke dashboard miliknya |
| Penilai membuka `/pemohon/dashboard` | Dialihkan ke dashboard miliknya |
| Keluar lalu membuka `/pemohon/projects` | Dialihkan ke halaman login |
| Pemohon membuka permohonan milik orang lain | *"Anda hanya dapat melihat permohonan milik sendiri."* |

**Pembuktian bahwa pembatasan ada di server, bukan sekadar di tampilan:**
```bash
curl -X POST http://localhost:8080/api/v1/projects/1/approve ^
  -H "Authorization: Bearer TOKEN_PEMOHON" ^
  -H "Accept: application/json"
```
Balasan: `{"success":false,"message":"Hanya penilai yang dapat melakukan penilaian."}`

---

### Langkah 15 — Tampilan Responsif

Buka Developer Tools (**F12**) → aktifkan mode perangkat (**Ctrl+Shift+M**):

| Lebar | Perangkat | Yang harus terjadi |
|-------|-----------|--------------------|
| 375 px | iPhone SE | Menu jadi hamburger, tabel bisa digeser mendatar |
| 768 px | iPad | Tata letak dua kolom |
| 1280 px | Laptop | Tata letak penuh dengan menu sisi kiri |

Periksa halaman: Landing, Login, Dashboard, Daftar Permohonan, Detail, Penilaian.

---

## Bagian 5 — Menjalankan Uji Otomatis

```bash
make test
```

Atau:
```bash
docker compose exec php php artisan test
```

Hasil yang diharapkan:
```
Tests:    132 passed (321 assertions)
```

Cakupan pengujian:

| Berkas Uji | Jumlah | Cakupan |
|------------|--------|---------|
| `AuthenticationTest` | 8 | Registrasi, login, logout |
| `ProjectCRUDTest` | 14 | Buat, baca, ubah, hapus, saring |
| `ProjectWorkflowTest` | 20 | Seluruh perpindahan status |
| `DocumentUploadTest` | 15 | Unggah, unduh, hapus, validasi |
| `DashboardTest` | 10 | Statistik kedua peran |
| `NotificationTest` | 13 | Notifikasi tiap peristiwa |
| `ExportTest` | 11 | Excel, PDF, pembatasan laju |
| Unit tests | 41 | Enum, model, kebijakan akses |

> Uji otomatis berjalan di database terpisah (`sipdok_testing`), sehingga
> **data demo Anda tidak akan terhapus**.

---

## Bagian 6 — Bila Terjadi Masalah

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| Setiap halaman lambat (±5 detik) | Cache belum diaktifkan | `make optimize` |
| Perubahan kode PHP tidak muncul | OPcache menyimpan versi lama | `make restart-php` |
| `Connection refused` di frontend | Backend belum jalan | `docker compose up -d` |
| Login selalu gagal | Database belum diisi | `docker compose exec php php artisan migrate:fresh --seed` |
| Notifikasi tidak muncul | Worker antrean mati | `docker compose restart queue` |
| Port 8080 sudah terpakai | Aplikasi lain memakai port | Hentikan aplikasi tersebut, atau ubah pemetaan port di `docker-compose.yml` |
| Halaman putih kosong | Berkas frontend belum lengkap | `npm install` lalu `npm run dev` ulang |
| Sebagian container mati setelah `git checkout`/`git merge` | `docker-compose.yml` ikut berubah, sebagian container berhenti | `docker compose up -d` lalu `make optimize` |

> **Selalu periksa `docker compose ps` sebelum mulai demo.** Kelima container
> harus berstatus `Up`. Jika API membalas `000` pada `curl`, artinya container
> `sipdok-nginx` atau `sipdok-php` sedang tidak berjalan.

**Menyetel ulang seluruh data demo:**
```bash
docker compose exec php php artisan migrate:fresh --seed
make optimize
```

**Menyalakan ulang semuanya dari nol:**
```bash
docker compose down
docker compose up -d
```

---

## Bagian 7 — Ringkasan Alur Status

```
                    ┌──────────────────────────────────┐
                    │                                  │
   Draft ──────► Diajukan ──────► Sedang Dinilai ──┬──► Disetujui  (final)
     ▲                                             │
     │                                             ├──► Ditolak    (final)
     │                                             │
     └────────── Perlu Revisi ◄────────────────────┘
                      │
                      └──► Diajukan (jumlah revisi bertambah)
```

| Perpindahan | Pelaku | Syarat |
|-------------|--------|--------|
| Draft → Diajukan | Pemohon | Minimal 1 dokumen terlampir |
| Diajukan → Sedang Dinilai | Penilai | Belum diambil penilai lain |
| Sedang Dinilai → Disetujui | Penilai | Hanya penilai yang menangani |
| Sedang Dinilai → Perlu Revisi | Penilai | Catatan minimal 10 karakter |
| Sedang Dinilai → Ditolak | Penilai | Catatan minimal 10 karakter |
| Perlu Revisi → Diajukan | Pemohon | Menambah jumlah revisi |

Disetujui dan Ditolak bersifat **final** — tidak dapat diubah lagi.

---

## Bagian 8 — Daftar Periksa Demo

Cetak atau ikuti daftar ini saat mendemokan:

- [ ] Landing page terbuka, penggeseran antar bagian berfungsi, FAQ bisa dibuka
- [ ] Registrasi Pemohon baru berhasil
- [ ] Validasi formulir menampilkan pesan yang jelas
- [ ] Login Pemohon → dashboard beserta grafiknya tampil
- [ ] Tekan F5 → sesi tetap bertahan
- [ ] Buat permohonan + unggah dokumen
- [ ] Submit tanpa dokumen → ditolak dengan pesan yang jelas
- [ ] Submit dengan dokumen → berhasil
- [ ] Login Penilai di Incognito
- [ ] Ambil berkas untuk dinilai
- [ ] Unduh dokumen lampiran
- [ ] Minta revisi tanpa catatan → ditolak
- [ ] Minta revisi dengan catatan → berhasil
- [ ] Pemohon menerima notifikasi dan mengajukan ulang
- [ ] Penilai menyetujui permohonan
- [ ] Permohonan yang disetujui tidak bisa diubah
- [ ] Notifikasi dapat ditandai dibaca
- [ ] Ekspor Excel berhasil
- [ ] Ekspor PDF berhasil
- [ ] Pencarian, penyaringan, pengurutan berfungsi
- [ ] Pembatasan akses antar peran berfungsi
- [ ] Tampilan rapi di lebar 375 px, 768 px, dan 1280 px
- [ ] `make test` → 132 uji lulus semua
