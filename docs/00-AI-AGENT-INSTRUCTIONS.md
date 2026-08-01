# AI Agent Instructions — SIPDOK Project

## Project Context
Anda sedang membangun **SIPDOK (Sistem Informasi Persetujuan Dokumen)** — aplikasi web full-stack untuk mengelola proses pengajuan dan persetujuan dokumen kelayakan di instansi pemerintah.

## Tech Stack (WAJIB)
- **Backend:** PHP 8.2+, Laravel 12, REST API
- **Frontend:** Vue 3 (Composition API + `<script setup>`), Pinia, Tailwind CSS
- **Database:** PostgreSQL 16
- **Auth:** Laravel Sanctum (SPA mode)
- **Authorization:** Spatie Laravel-Permission
- **Cache:** Redis
- **Queue:** Redis driver
- **Charts:** ApexCharts (vue3-apexcharts)
- **Export:** Maatwebsite Laravel-Excel, barryvdh/laravel-dompdf
- **Container:** Docker + Docker Compose
- **CI/CD:** GitLab CI

## Project Structure
```
sipdok/
├── backend/     → Laravel 12 (API only, no Blade)
├── frontend/    → Vue 3 SPA (Vite)
├── docker/      → Dockerfiles & nginx config
├── docs/        → Planning documents
├── docker-compose.yml
├── Makefile
└── README.md
```

## Core Business Rules (HARUS DIPATUHI)

### Status Machine
```
draft → submitted → in_review → approved (FINAL)
                               → revised → submitted (LOOP)
                               → rejected (FINAL)
```

### Roles
1. **Pemohon:** Create & submit projects, upload documents, view own data only
2. **Penilai:** Review all submissions, approve/revise/reject, view all data

### Key Constraints
- Pemohon can only edit projects with status `draft` or `revised`
- Must have ≥1 document to submit
- Revise & reject REQUIRE notes (min 10 chars)
- One reviewer per project at a time (current_reviewer_id)
- Project code format: `PRJ-{YYYY}-{NNNNN}`

## Coding Standards (WAJIB)

### Backend (Laravel)
- **Pattern:** Controller → Service → Model (thin controllers)
- **Validation:** Always use Form Request classes, never validate in controller
- **Response:** Always use API Resource classes for transformation
- **Auth:** Sanctum middleware on all API routes except login/register
- **Enum:** Use PHP 8.1 Enum for ProjectStatus and ProjectPriority
- **Eager Loading:** ALWAYS use `with()` to prevent N+1 queries
- **Caching:** Redis with tagged cache, invalidate via Model Observer
- **Queue:** All notifications and heavy operations via Queue jobs
- **PSR-12:** Strict compliance, `declare(strict_types=1)` on all files
- **Type hints:** All parameters and return types must have type declarations

### Frontend (Vue 3)
- **Composition API** with `<script setup>` syntax only
- **Pinia** for state management (auth, projects, notifications, ui stores)
- **Axios** with interceptors for API calls (centralized in `src/api/`)
- **Composables** for reusable logic (`useAuth`, `useProjects`, etc.)
- **Tailwind CSS** for styling (no CSS frameworks like Bootstrap)
- **Headless UI** for accessible interactive components
- **Lazy loading** routes with dynamic imports
- **Debounce** search inputs (300ms)

### Database (PostgreSQL)
- Composite indexes on frequently filtered columns
- CHECK constraints for status and priority enums
- Partial indexes for active/unread records
- Foreign key constraints with proper ON DELETE behavior
- All timestamp columns use TIMESTAMP type

### Git
- Commit format: `<type>: <description>` (feat, fix, refactor, test, docs, chore, perf)
- Feature branches from `develop`: `feature/database-models`, `feature/auth-api`, etc.
- Merge to `develop`, then `develop` → `main` when stable

## Data Requirements
- Seed 1.000 Pemohon users + 1.000 Penilai users
- Seed 10.000 projects with realistic status distribution
- Test accounts: `pemohon@sipdok.test` / `password` and `penilai@sipdok.test` / `password`
- Use batch insert (chunks of 500) for seeder performance

## API Design
- Base URL: `/api/v1/`
- Standard response: `{ success: bool, message: string, data: any, meta?: pagination }`
- HTTP status codes: 200, 201, 400, 401, 403, 404, 422, 429, 500
- Rate limiting: 5/min for auth, 60/min for general, 5/min for export
- Pagination: default 15, max 100

## Performance Targets
- List API: < 200ms response time
- Detail API: < 100ms response time
- Dashboard: cached in Redis (TTL 5 minutes)
- Document categories: cached (TTL 1 hour)

## Reference Documents
When you need details, refer to these docs in the `docs/` folder:
- `01-PRD.md` — Full product requirements
- `02-DATABASE.md` — Database schema, indexes, constraints
- `03-API.md` — REST API endpoint documentation
- `04-ARCHITECTURE.md` — Architecture decisions & project structure
- `05-FLOW-AND-RULES.md` — Business flow & all business rules
- `06-UI-DESIGN.md` — UI/UX design specifications & wireframes
- `07-PROGRESS.md` — Task checklist & progress tracker
- `08-TESTING.md` — Test plan & test cases
- `09-DOCKER-CICD.md` — Docker & CI/CD configuration
- `10-README-TEMPLATE.md` — README template
- `11-CONVENTIONS.md` — Coding conventions & standards
- `12-SEEDER-STRATEGY.md` — Data seeding strategy
