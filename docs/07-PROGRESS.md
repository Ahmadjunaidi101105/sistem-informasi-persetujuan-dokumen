# Progress Tracker — SIPDOK

## Development Phases & Tasks

### Phase 1: Project Setup & Infrastructure
- [x] Initialize Laravel 12 project (backend/)
- [x] Initialize Vue 3 + Vite project (frontend/)
- [x] Docker setup (docker-compose.yml with PHP, Nginx, PostgreSQL, Redis)
- [x] Configure .env files
- [x] Git init, .gitignore, initial commit
- [x] Create branch: `main`, `develop`, `feature/*`
- [x] Setup Makefile with convenience commands
- [x] Configure CORS for SPA
- [x] Install & configure Laravel Sanctum
- [x] Install & configure Spatie Permission
- [x] Install frontend dependencies (Pinia, Axios, Tailwind, ApexCharts, Headless UI)
- [x] Configure Tailwind CSS with custom theme
- [x] Setup Axios instance with interceptors

### Phase 2: Database & Models (Bobot: 20%)
- [x] Create migration: users (extend default)
- [x] Create migration: document_categories
- [x] Create migration: projects (with indexes & constraints)
- [x] Create migration: project_documents
- [x] Create migration: project_reviews
- [x] Create migration: notifications (jsonb)
- [x] Add PostgreSQL CHECK constraints
- [x] Add composite indexes
- [x] Add partial indexes
- [x] Create Model: User (with relationships, scopes)
- [x] Create Model: DocumentCategory
- [x] Create Model: Project (with relationships, scopes, status enum)
- [x] Create Model: ProjectDocument
- [x] Create Model: ProjectReview
- [x] Create Enum: ProjectStatus
- [x] Create Enum: ProjectPriority
- [x] Create Factory: UserFactory
- [x] Create Factory: ProjectFactory
- [x] Create Factory: ProjectDocumentFactory
- [x] Create Factory: ProjectReviewFactory
- [x] Create Factory: DocumentCategoryFactory
- [x] Create Seeder: RolesAndPermissionsSeeder
- [x] Create Seeder: DocumentCategorySeeder
- [x] Create Seeder: UserSeeder (1000 pemohon + 1000 penilai)
- [x] Create Seeder: ProjectSeeder (10000 projects with documents & reviews)
- [x] Run migrations & seeders, verify data integrity

### Phase 3: Backend API (Bobot: 25%)
- [x] Create ApiResponse helper/trait
- [x] Create base Controller
- [x] **Auth Endpoints**
  - [x] POST /auth/register
  - [x] POST /auth/login (with rate limiting)
  - [x] POST /auth/logout
  - [x] GET /auth/user
- [x] **Form Requests**
  - [x] RegisterRequest
  - [x] LoginRequest
  - [x] StoreProjectRequest
  - [x] UpdateProjectRequest
  - [x] UploadDocumentRequest
  - [x] ReviewActionRequest
- [x] **API Resources**
  - [x] UserResource
  - [x] ProjectResource / ProjectCollection
  - [x] ProjectDocumentResource
  - [x] ProjectReviewResource
  - [x] NotificationResource
  - [~] DashboardResource — tidak dibuat sebagai kelas terpisah. Payload dashboard
        adalah agregat (stats, distribusi, tren), bukan entitas; bagian yang berupa
        entitas (`recent_projects`, `recent_reviews`) sudah melewati ProjectResource.
- [x] **Project Endpoints**
  - [x] GET /projects (with filtering, sorting, pagination)
  - [x] POST /projects
  - [x] GET /projects/{id}
  - [x] PUT /projects/{id}
  - [x] DELETE /projects/{id}
- [x] **Status Transition Endpoints**
  - [x] POST /projects/{id}/submit
  - [x] POST /projects/{id}/take-review
  - [x] POST /projects/{id}/approve
  - [x] POST /projects/{id}/revise
  - [x] POST /projects/{id}/reject
- [x] **Document Endpoints**
  - [x] POST /projects/{id}/documents
  - [x] GET /projects/{id}/documents
  - [x] GET /documents/{id}/download
  - [x] DELETE /documents/{id}
- [x] **Review/History Endpoints**
  - [x] GET /projects/{id}/reviews
  - [x] GET /reviews (all reviews for Penilai)
- [x] **Notification Endpoints**
  - [x] GET /notifications
  - [x] POST /notifications/{id}/read
  - [x] POST /notifications/read-all
- [x] **Dashboard Endpoints**
  - [x] GET /dashboard/pemohon
  - [x] GET /dashboard/penilai
- [x] **Master Data Endpoints**
  - [x] GET /document-categories
- [x] **Export Endpoints**
  - [x] GET /export/projects (Excel)
  - [x] GET /export/projects/{id}/pdf
- [x] **Services**
  - [x] ProjectService
  - [x] ReviewService
  - [x] DashboardService
  - [x] ExportService
- [x] **Policies**
  - [x] ProjectPolicy
  - [x] ProjectDocumentPolicy
- [x] **Middleware**
  - [x] Role enforcement — dilakukan lewat Laravel Policy + Gate (`ProjectPolicy`,
        `ProjectDocumentPolicy`), bukan middleware terpisah. Policy dipilih karena
        aturan akses di sini bergantung pada state record (status, pemilik,
        penilai yang sedang menangani), bukan sekadar peran user.
  - [x] Rate limiting config — 60 req/menit umum (`throttleApi` + limiter `api`),
        5 req/menit untuk login dan endpoint export. Terverifikasi: request ke-61
        mengembalikan 429.
- [x] Route definitions (api.php)

### Phase 4: Performance Optimization (Bobot: 20%)
- [x] Eager loading di semua query yang butuh relasi (`with()`/`withCount()` di
      ProjectService, ReviewService, DashboardService, DocumentController)
- [x] Offset pagination (LengthAwarePaginator) dengan `meta` konsisten di semua
      endpoint list
- [x] Redis caching untuk dashboard (TTL 5 menit)
- [x] Redis caching untuk document_categories (TTL 1 jam)
- [x] Cache invalidation via Model Observer
- [x] Queue setup (Redis driver) — worker berjalan sebagai service `sipdok-queue`
  - [~] UploadDocumentJob — tidak dipakai. Upload harus mengembalikan metadata
        dokumen secara sinkron agar UI bisa langsung menampilkannya.
  - [x] SendNotificationJob — seluruh Notification mengimplementasikan
        `ShouldQueue`, sehingga otomatis diproses lewat queue.
  - [~] ExportProjectsJob — export tetap sinkron dengan indikator loading di UI.
- [x] Notifications via Queue
  - [x] ProjectSubmittedNotification
  - [x] ProjectTakenForReviewNotification
  - [x] ProjectApprovedNotification
  - [x] ProjectRevisedNotification
  - [x] ProjectRejectedNotification
- [x] Root cause latency ditemukan & diperbaiki: bukan query, melainkan I/O file
      pada bind-mount Docker (~2.2ms per stat vs ~0.16ms di filesystem container).
      OPcache `validate_timestamps=0` + `config:cache`/`route:cache`.
- [x] API response time check — terukur pada data 10.000 project / 2.000 user:
      | Endpoint            | Sebelum | Sesudah |
      |---------------------|---------|---------|
      | GET /projects       | 5,44s   | 0,26s   |
      | GET /reviews        | 5,42s   | 0,21s   |
      | GET /dashboard/*    | 5,73s   | 0,18s   |
      | GET /document-categories | 12,93s | 0,17s |
- [x] Export Excel dioptimalkan (ShouldAutoSize dihapus): 15,4s → 12,2s untuk
      9.000 baris, 3,7s untuk export terfilter.

### Phase 5: Frontend Core & Layouts (Bobot: 15%)
- [x] Setup Vue 3 + Vite + Tailwind + Pinia + Vue Router
- [x] Configure API Client (Axios) dengan interceptors (auth token, error handling)
- [x] Buat Pinia stores:
  - [x] auth.js
  - [x] ui.js
  - [x] notifications.js
- [x] Setup Utils (formatters, constants)
- [x] Buat Layout Components:
  - [x] AuthLayout.vue
  - [x] DashboardLayout.vue
- [x] **Common Components**
  - [x] Sidebar.vue
  - [x] Navbar.vue (with NotificationBell)
  - [x] DataTable.vue
  - [x] LoadingSpinner.vue
  - [x] StatusBadge.vue
  - [x] ConfirmDialog.vue
  - [x] EmptyState.vue
  - [x] Toast.vue
  - [x] Pagination.vue
  - [x] SearchInput.vue
  - [x] FileUpload.vue
- [x] **Stores (Pinia)**
  - [x] auth store
  - [x] notifications store
  - [x] ui store (sidebar toggle, toast)
  - [~] projects store — tidak dibuat. State project bersifat per-halaman
        (filter, pagination, sorting berbeda tiap halaman) sehingga menyimpannya
        di store global justru menimbulkan state basi antar halaman.
- [~] **Composables** — tidak dibuat sebagai file terpisah. Logika yang
      direncanakan sudah tercakup: auth/notifikasi/toast di Pinia store,
      pagination & fetching di masing-masing halaman. Membuat wrapper tipis
      di atas store hanya menambah lapisan tanpa mengurangi duplikasi.
- [x] **API Layer**
  - [x] client.js (Axios config + interceptor auth & error)
  - [x] auth.js
  - [x] projects.js
  - [x] reviews.js
  - [x] dashboard.js
  - [x] notifications.js
  - [x] exports.js
  - [x] categories.js
  - [x] documents.js

### Phase 6: Pages Implementation (Bobot: 20%)
- [x] **Auth Pages**
  - [x] Login.vue
  - [x] Register.vue
- [x] **Pemohon Pages**
  - [x] DashboardPage.vue (stats + charts + recent)
  - [x] ProjectListPage.vue (filter, search, sort, pagination)
  - [x] ProjectCreatePage.vue (form + file upload)
  - [x] ProjectEditPage.vue
  - [x] ProjectDetailPage.vue (tabs: info, docs, history)
- [x] **Penilai Pages**
  - [x] DashboardPage.vue (stats + 3 charts + recent reviews)
  - [x] SubmissionListPage.vue (all submitted projects)
  - [x] ReviewPage.vue (review form with approve/revise/reject)
  - [x] ReviewHistoryPage.vue
- [x] **Shared Pages**
  - [x] NotificationsPage.vue
- [x] **Router**
  - [x] Route definitions with guards & role checks
  - [x] Lazy loading (dynamic imports)
- [x] Landing page publik (`/`) — hero, fitur, alur proses, peran, FAQ, CTA, footer
- [x] Responsive design testing

### Phase 6: Dashboard & Visualization (Bobot: 5%)
- [x] ApexCharts integration
- [x] Pemohon: Bar chart (status distribution)
- [x] Pemohon: Line chart (monthly submissions)
- [x] Penilai: Donut chart (approval rate)
- [x] Penilai: Multi-line chart (monthly trends by decision)
- [x] Penilai: Horizontal bar chart (by category)
- [x] StatCard component with icons and trends
- [x] Semua chart memakai palet brand + punya empty state

### Phase 7: Code Quality (Bobot: 5%)
- [x] PSR-12 compliance check (PHP CS Fixer)
- [x] Consistent naming conventions
- [x] PHPDoc comments on services & complex methods
- [x] JSDoc comments on composables & API layer
- [x] Remove all console.log / dd() / dump()
- [x] Error handling consistency
- [x] No hardcoded values (use constants/enums/config)

### Phase 8: Testing
- [x] **Unit Tests**
  - [x] ProjectStatusTransitionTest
  - [x] ProjectCodeGenerationTest
  - [x] ProjectPolicyTest
- [x] **Feature Tests**
  - [x] AuthenticationTest (register, login, logout)
  - [x] ProjectCRUDTest
  - [x] ProjectWorkflowTest (submit, review, approve, revise, reject)
  - [x] DocumentUploadTest (15 test)
  - [x] DashboardTest (10 test)
  - [x] NotificationTest (13 test)
  - [x] ExportTest (11 test)
- [x] Run full test suite, ensure all pass — **132 test, 321 assertion, semua lulus**
- [x] Test suite terisolasi di database `sipdok_testing` (tidak lagi menghapus data dev)

- [x] Verify docker-compose up from scratch works

### Phase 9: Docker & CI/CD
- [x] Dockerfile for PHP-FPM
- [x] Dockerfile for Node (frontend build)
- [x] Nginx config
- [x] docker-compose.yml (php, nginx, postgres, redis, node)
- [x] docker-compose up runs the full stack
- [x] .gitlab-ci.yml with stages:
  - [x] lint (phpcs, eslint)
  - [x] test (phpunit)
  - [x] build (frontend build)
- [x] Verify docker-compose up from scratch works
### Phase 10: Documentation & Git (Bobot: 5%)
- [x] README.md (comprehensive)
  - [x] Project description
  - [x] Tech stack
  - [x] Prerequisites
  - [x] Installation steps
  - [x] Docker setup
  - [x] Seeder commands
  - [x] API documentation link
  - [x] Test credentials
  - [x] Screenshots
- [x] API Documentation (Postman collection or API.md)
- [x] Git history review
  - [x] Meaningful commit messages
  - [x] Feature branches merged
  - [x] Clean branch history
- [x] Database schema diagram (ER diagram)
- [x] Final review & cleanup
- [x] Deep performance optimization (Partial Indexes, Eager Loading Check)
- [x] PostgreSQL Full-Text Search (Bonus)
- [x] Comprehensive Seeder Verification (10.000 Projects, 2.000 Users)
- [x] Comprehensive Error Handling & Edge Cases (Bonus)
- [x] UI Polish & Responsive Design (Mobile & Tablet Support)

## ✅ PROJECT COMPLETED — v1.0.0

## Git Branch Strategy

```
main           ← Production-ready code
  └── develop  ← Integration branch
        ├── feature/setup-infrastructure
        ├── feature/database-models
        ├── feature/auth-api
        ├── feature/project-api
        ├── feature/workflow-api
        ├── feature/document-upload
        ├── feature/notifications
        ├── feature/dashboard-api
        ├── feature/export
        ├── feature/caching-queue
        ├── feature/frontend-setup
        ├── feature/frontend-auth
        ├── feature/frontend-pemohon
        ├── feature/frontend-penilai
        ├── feature/frontend-dashboard
        ├── feature/testing
        ├── feature/docker
        ├── feature/cicd
        └── feature/documentation
```

## Commit Message Convention

```
feat: add user registration API
fix: correct project status transition validation
refactor: extract project service from controller
test: add project workflow feature tests
docs: update README with installation steps
chore: configure Docker setup
style: apply PSR-12 formatting
perf: add Redis caching for dashboard
```
