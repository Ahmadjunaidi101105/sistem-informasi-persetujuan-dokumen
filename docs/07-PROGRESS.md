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
  - [ ] POST /auth/register
  - [ ] POST /auth/login (with rate limiting)
  - [ ] POST /auth/logout
  - [ ] GET /auth/user
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
  - [ ] DashboardResource
- [ ] **Project Endpoints**
  - [ ] GET /projects (with filtering, sorting, pagination)
  - [ ] POST /projects
  - [ ] GET /projects/{id}
  - [ ] PUT /projects/{id}
  - [ ] DELETE /projects/{id}
- [ ] **Status Transition Endpoints**
  - [ ] POST /projects/{id}/submit
  - [ ] POST /projects/{id}/take-review
  - [ ] POST /projects/{id}/approve
  - [ ] POST /projects/{id}/revise
  - [ ] POST /projects/{id}/reject
- [ ] **Document Endpoints**
  - [ ] POST /projects/{id}/documents
  - [ ] GET /projects/{id}/documents
  - [ ] GET /documents/{id}/download
  - [ ] DELETE /documents/{id}
- [ ] **Review/History Endpoints**
  - [ ] GET /projects/{id}/reviews
  - [ ] GET /reviews (all reviews for Penilai)
- [ ] **Notification Endpoints**
  - [ ] GET /notifications
  - [ ] POST /notifications/{id}/read
  - [ ] POST /notifications/read-all
- [ ] **Dashboard Endpoints**
  - [ ] GET /dashboard/pemohon
  - [ ] GET /dashboard/penilai
- [ ] **Master Data Endpoints**
  - [ ] GET /document-categories
- [ ] **Export Endpoints**
  - [ ] GET /export/projects (Excel)
  - [ ] GET /export/projects/{id}/pdf
- [ ] **Services**
  - [ ] ProjectService
  - [ ] ReviewService
  - [ ] DashboardService
  - [ ] ExportService
- [x] **Policies**
  - [x] ProjectPolicy
  - [x] ProjectDocumentPolicy
- [ ] **Middleware**
  - [ ] EnsureRole middleware (or use Spatie middleware)
  - [ ] Rate limiting config
- [x] Route definitions (api.php)

### Phase 4: Performance Optimization (Bobot: 20%)
- [ ] Eager loading di semua query yang butuh relasi
- [ ] Implement cursor/offset pagination
- [ ] Redis caching untuk dashboard (TTL 5 menit)
- [ ] Redis caching untuk document_categories (TTL 1 jam)
- [ ] Cache invalidation via Model Observer
- [ ] Queue setup (Redis driver)
  - [ ] UploadDocumentJob
  - [ ] SendNotificationJob
  - [ ] ExportProjectsJob
- [ ] Notifications via Queue
  - [ ] ProjectSubmittedNotification
  - [ ] ProjectTakenForReviewNotification
  - [ ] ProjectApprovedNotification
  - [ ] ProjectRevisedNotification
  - [ ] ProjectRejectedNotification
- [ ] Database query optimization (EXPLAIN ANALYZE critical queries)
- [ ] API response time check (< 200ms list, < 100ms single)
- [ ] N+1 query detection & fix

### Phase 5: Frontend Vue (Bobot: 25%)
- [ ] **Layouts**
  - [ ] AuthLayout.vue
  - [ ] DashboardLayout.vue (Sidebar + Navbar)
- [ ] **Common Components**
  - [ ] Sidebar.vue
  - [ ] Navbar.vue (with NotificationBell)
  - [ ] DataTable.vue
  - [ ] Pagination.vue
  - [ ] StatusBadge.vue
  - [ ] FileUpload.vue (drag & drop)
  - [ ] ConfirmDialog.vue
  - [ ] LoadingSpinner.vue
  - [ ] EmptyState.vue
  - [ ] Toast.vue
  - [ ] SearchInput.vue
- [ ] **Stores (Pinia)**
  - [ ] auth store
  - [ ] projects store
  - [ ] notifications store
  - [ ] ui store (sidebar toggle, toast)
- [ ] **Composables**
  - [ ] useAuth
  - [ ] useProjects
  - [ ] useNotifications
  - [ ] usePagination
  - [ ] useToast
- [ ] **API Layer**
  - [ ] client.js (Axios config)
  - [ ] auth.js
  - [ ] projects.js
  - [ ] reviews.js
  - [ ] dashboard.js
  - [ ] notifications.js
  - [ ] exports.js
- [ ] **Auth Pages**
  - [ ] LoginPage.vue
  - [ ] RegisterPage.vue
- [ ] **Pemohon Pages**
  - [ ] DashboardPage.vue (stats + charts + recent)
  - [ ] ProjectListPage.vue (filter, search, sort, pagination)
  - [ ] ProjectCreatePage.vue (form + file upload)
  - [ ] ProjectEditPage.vue
  - [ ] ProjectDetailPage.vue (tabs: info, docs, history)
- [ ] **Penilai Pages**
  - [ ] DashboardPage.vue (stats + 3 charts + recent reviews)
  - [ ] SubmissionListPage.vue (all submitted projects)
  - [ ] ReviewPage.vue (review form with approve/revise/reject)
  - [ ] ReviewHistoryPage.vue
- [ ] **Shared Pages**
  - [ ] NotificationsPage.vue
- [ ] **Router**
  - [ ] Route definitions with guards & role checks
  - [ ] Lazy loading (dynamic imports)
- [ ] Responsive design testing

### Phase 6: Dashboard & Visualization (Bobot: 5%)
- [ ] ApexCharts integration
- [ ] Pemohon: Bar chart (status distribution)
- [ ] Pemohon: Line chart (monthly submissions)
- [ ] Penilai: Donut chart (approval rate)
- [ ] Penilai: Multi-line chart (monthly trends by decision)
- [ ] Penilai: Horizontal bar chart (by category)
- [ ] StatCard component with icons and trends

### Phase 7: Code Quality (Bobot: 5%)
- [ ] PSR-12 compliance check (PHP CS Fixer)
- [ ] Consistent naming conventions
- [ ] PHPDoc comments on services & complex methods
- [ ] JSDoc comments on composables & API layer
- [ ] Remove all console.log / dd() / dump()
- [ ] Error handling consistency
- [ ] No hardcoded values (use constants/enums/config)

### Phase 8: Testing
- [ ] **Unit Tests**
  - [ ] ProjectStatusTransitionTest
  - [ ] ProjectCodeGenerationTest
  - [ ] ProjectPolicyTest
- [ ] **Feature Tests**
  - [ ] AuthenticationTest (register, login, logout)
  - [ ] ProjectCRUDTest
  - [ ] ProjectWorkflowTest (submit, review, approve, revise, reject)
  - [ ] DocumentUploadTest
  - [ ] DashboardTest
  - [ ] NotificationTest
  - [ ] ExportTest
- [ ] Run full test suite, ensure all pass

### Phase 9: Docker & CI/CD
- [ ] Dockerfile for PHP-FPM
- [ ] Dockerfile for Node (frontend build)
- [ ] Nginx config
- [ ] docker-compose.yml (php, nginx, postgres, redis, node)
- [ ] docker-compose up runs the full stack
- [ ] .gitlab-ci.yml with stages:
  - [ ] lint (phpcs, eslint)
  - [ ] test (phpunit)
  - [ ] build (frontend build)
- [ ] Verify docker-compose up from scratch works

### Phase 10: Documentation & Git (Bobot: 5%)
- [ ] README.md (comprehensive)
  - [ ] Project description
  - [ ] Tech stack
  - [ ] Prerequisites
  - [ ] Installation steps
  - [ ] Docker setup
  - [ ] Seeder commands
  - [ ] API documentation link
  - [ ] Test credentials
  - [ ] Screenshots
- [ ] API Documentation (Postman collection or API.md)
- [ ] Git history review
  - [ ] Meaningful commit messages
  - [ ] Feature branches merged
  - [ ] Clean branch history
- [ ] Database schema diagram (ER diagram)
- [ ] Final review & cleanup

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
