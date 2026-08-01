# SIPDOK — Sistem Informasi Persetujuan Dokumen

Aplikasi web full-stack untuk mengelola proses pengajuan, penilaian, dan persetujuan dokumen kelayakan pada instansi pemerintah.

## Technology Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.3, Laravel 12 |
| Frontend | Vue 3 (Composition API), Pinia, Tailwind CSS |
| Database | PostgreSQL 16 |
| Cache & Queue | Redis 7 |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel-Permission |
| Charts | ApexCharts |
| Export | Laravel Excel, DomPDF |
| Container | Docker, Docker Compose |
| CI/CD | GitLab CI |

## Features

- Login & Register (Pemohon)
- Role-based access control (Pemohon & Penilai)
- CRUD Project Permohonan Dokumen
- Upload dokumen pendukung (PDF, DOC, DOCX, JPG, PNG — max 10MB)
- Workflow approval multi-tahap (Draft → Submitted → In Review → Approved/Revised/Rejected)
- Review & penilaian dokumen oleh Penilai
- History / audit trail lengkap per project
- Dashboard dengan visualisasi data (ApexCharts)
- In-app notifications (queued)
- Export data ke Excel dan PDF
- Redis caching untuk performa
- Queue processing untuk upload & notifikasi
- Docker containerization
- Comprehensive API test suite

## Prerequisites

- Docker & Docker Compose
- Git

## Quick Start (Docker)

```bash
# 1. Clone repository
git clone https://github.com/Ahmadjunaidi101105/sistem-informasi-persetujuan-dokumen.git
cd sistem-informasi-persetujuan-dokumen/sipdok

# 2. Copy environment file
cp backend/.env.example backend/.env

# 3. Start all services
docker-compose up -d

# 4. Setup application
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate:fresh --seed
docker-compose exec php php artisan storage:link
docker-compose run --rm node npm install
docker-compose run --rm node npm run build

# 5. Application ready!
# Backend API: http://localhost:8080/api/v1
# Frontend: http://localhost:5173 (dev) atau http://localhost:8080 (production)
```

## Local Development (Tanpa Docker)

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Pastikan PostgreSQL & Redis sudah berjalan
# Update .env dengan kredensial lokal

php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve          # http://localhost:8000
php artisan queue:work     # (terminal terpisah)
```

### Frontend
```bash
cd frontend
npm install
npm run dev                # http://localhost:5173
```

## Test Credentials

> **⚠️ PERINGATAN KEAMANAN (SECURITY WARNING)**
> Akun dan kata sandi di bawah ini hanya boleh digunakan untuk lingkungan pengembangan (Development) dan Demo (Staging). **JANGAN** menggunakan *seeder* atau *default password* ini di lingkungan Produksi (Production).

| Role | Email | Password |
|------|-------|----------|
| Pemohon | pemohon@sipdok.test | password |
| Penilai | penilai@sipdok.test | password |

## Seeded Data

| Data | Jumlah |
|------|--------|
| User Pemohon | 1.000 |
| User Penilai | 1.000 |
| Project Permohonan | 10.000 |
| Kategori Dokumen | 10 |

## Running Tests

```bash
# Via Docker
docker-compose exec php php artisan test

# Via PHP
cd backend
php artisan test

# Specific test
php artisan test --filter=ProjectWorkflowTest

# With coverage
php artisan test --coverage
```

## API Documentation

Lihat [docs/API-DOCUMENTATION.md](docs/API-DOCUMENTATION.md) untuk dokumentasi REST API lengkap.

### Base URL
```
http://localhost:8080/api/v1
```

### Auth Flow (Sanctum SPA)
```
1. GET  /sanctum/csrf-cookie     (set CSRF token)
2. POST /api/v1/auth/login       (login)
3. GET  /api/v1/auth/user        (get profile)
```

### Key Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | /auth/register | Register Pemohon |
| POST | /auth/login | Login |
| GET | /projects | List projects |
| POST | /projects | Create project |
| POST | /projects/{id}/submit | Submit permohonan |
| POST | /projects/{id}/approve | Approve (Penilai) |
| POST | /projects/{id}/revise | Request revisi (Penilai) |
| POST | /projects/{id}/reject | Reject (Penilai) |
| GET | /dashboard/pemohon | Dashboard Pemohon |
| GET | /dashboard/penilai | Dashboard Penilai |

## Project Structure

```
sipdok/
├── backend/          # Laravel 12 API
├── frontend/         # Vue 3 SPA
├── docker/           # Docker configs
├── docs/             # Documentation
├── docker-compose.yml
├── Makefile
└── README.md
```

## Git Branch Strategy

- `main` — Production-ready code
- `develop` — Integration branch
- `feature/*` — Feature branches

## Database Schema

[Lihat docs/02-DATABASE.md](docs/02-DATABASE.md)

## Screenshots

> *[Tambahkan screenshots setelah development selesai]*

## License

Private — Technical Test Assessment
