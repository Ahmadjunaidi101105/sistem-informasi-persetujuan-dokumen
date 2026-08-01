# Architecture Decisions — SIPDOK

## 1. Project Structure

### 1.1 Monorepo Layout
```
sipdok/
├── backend/                    # Laravel 12 application
│   ├── app/
│   │   ├── Console/
│   │   ├── Enums/              # Status enum, Priority enum
│   │   ├── Events/             # Domain events
│   │   ├── Exceptions/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/
│   │   │   │       └── V1/     # Versioned API controllers
│   │   │   ├── Middleware/
│   │   │   ├── Requests/       # Form Request validation classes
│   │   │   └── Resources/      # API Resource transformers
│   │   ├── Jobs/               # Queue jobs
│   │   ├── Listeners/
│   │   ├── Models/
│   │   ├── Notifications/
│   │   ├── Observers/          # Model observers for cache invalidation
│   │   ├── Policies/           # Authorization policies
│   │   ├── Providers/
│   │   ├── Services/           # Business logic services
│   │   │   ├── ProjectService.php
│   │   │   ├── ReviewService.php
│   │   │   ├── DashboardService.php
│   │   │   └── ExportService.php
│   │   └── Traits/             # Reusable traits
│   ├── config/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   ├── storage/
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   ├── .env.example
│   ├── composer.json
│   └── phpunit.xml
│
├── frontend/                   # Vue 3 application
│   ├── public/
│   ├── src/
│   │   ├── api/                # API service layer (Axios instances)
│   │   │   ├── client.js       # Axios config + interceptors
│   │   │   ├── auth.js
│   │   │   ├── projects.js
│   │   │   ├── reviews.js
│   │   │   ├── dashboard.js
│   │   │   └── notifications.js
│   │   ├── assets/
│   │   │   └── css/
│   │   │       └── tailwind.css
│   │   ├── components/
│   │   │   ├── common/         # Shared components
│   │   │   │   ├── AppLayout.vue
│   │   │   │   ├── Sidebar.vue
│   │   │   │   ├── Navbar.vue
│   │   │   │   ├── DataTable.vue
│   │   │   │   ├── Pagination.vue
│   │   │   │   ├── StatusBadge.vue
│   │   │   │   ├── FileUpload.vue
│   │   │   │   ├── ConfirmDialog.vue
│   │   │   │   ├── LoadingSpinner.vue
│   │   │   │   ├── EmptyState.vue
│   │   │   │   ├── NotificationBell.vue
│   │   │   │   └── Toast.vue
│   │   │   ├── dashboard/
│   │   │   │   ├── StatCard.vue
│   │   │   │   ├── StatusChart.vue
│   │   │   │   ├── TrendChart.vue
│   │   │   │   └── CategoryChart.vue
│   │   │   └── project/
│   │   │       ├── ProjectForm.vue
│   │   │       ├── ProjectDetail.vue
│   │   │       ├── ProjectTimeline.vue
│   │   │       ├── DocumentList.vue
│   │   │       ├── ReviewForm.vue
│   │   │       └── ReviewHistory.vue
│   │   ├── composables/        # Vue 3 composables
│   │   │   ├── useAuth.js
│   │   │   ├── useProjects.js
│   │   │   ├── useNotifications.js
│   │   │   ├── usePagination.js
│   │   │   └── useToast.js
│   │   ├── layouts/
│   │   │   ├── AuthLayout.vue
│   │   │   └── DashboardLayout.vue
│   │   ├── pages/
│   │   │   ├── auth/
│   │   │   │   ├── LoginPage.vue
│   │   │   │   └── RegisterPage.vue
│   │   │   ├── pemohon/
│   │   │   │   ├── DashboardPage.vue
│   │   │   │   ├── ProjectListPage.vue
│   │   │   │   ├── ProjectCreatePage.vue
│   │   │   │   ├── ProjectEditPage.vue
│   │   │   │   └── ProjectDetailPage.vue
│   │   │   ├── penilai/
│   │   │   │   ├── DashboardPage.vue
│   │   │   │   ├── SubmissionListPage.vue
│   │   │   │   ├── ReviewPage.vue
│   │   │   │   └── ReviewHistoryPage.vue
│   │   │   └── NotificationsPage.vue
│   │   ├── router/
│   │   │   └── index.js
│   │   ├── stores/             # Pinia stores
│   │   │   ├── auth.js
│   │   │   ├── projects.js
│   │   │   ├── notifications.js
│   │   │   └── ui.js
│   │   ├── utils/
│   │   │   ├── constants.js
│   │   │   ├── formatters.js
│   │   │   └── validators.js
│   │   ├── App.vue
│   │   └── main.js
│   ├── index.html
│   ├── package.json
│   ├── tailwind.config.js
│   ├── vite.config.js
│   └── vitest.config.js
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── Dockerfile
│   └── node/
│       └── Dockerfile
│
├── docker-compose.yml
├── .gitlab-ci.yml
├── Makefile                    # Convenience commands
├── README.md
└── docs/                       # Planning documents (these files)
```

## 2. Key Architecture Decisions

### ADR-001: Monorepo vs Separate Repos
**Decision:** Monorepo (backend + frontend in single repo)
**Rationale:** Simpler deployment, shared Git history, easier for technical test review. Docker Compose manages both services.

### ADR-002: Laravel Sanctum vs JWT
**Decision:** Laravel Sanctum (SPA mode)
**Rationale:** First-class Laravel support, simpler setup for SPA, cookie-based auth with CSRF protection. No token management needed on frontend.

### ADR-003: Vue 3 Composition API vs Options API
**Decision:** Composition API with `<script setup>`
**Rationale:** Better TypeScript support potential, better code organization with composables, more reusable logic patterns. Industry standard for Vue 3 projects.

### ADR-004: Pinia vs Vuex
**Decision:** Pinia
**Rationale:** Official Vue 3 state management, simpler API, better TypeScript support, modular by design.

### ADR-005: Tailwind CSS vs Component Library
**Decision:** Tailwind CSS + Headless UI
**Rationale:** Full control over design, smaller bundle, professional look without being tied to a component library aesthetic. Headless UI for accessible interactive components (dropdowns, modals, dialogs).

### ADR-006: Service Layer Pattern
**Decision:** Controller → Service → Model
**Rationale:** Controllers stay thin (validation + response only). Business logic in Services for testability and reusability. Models handle relationships and scopes only.

### ADR-007: API Versioning
**Decision:** URL-based versioning (/api/v1/)
**Rationale:** Clear, explicit, easy to manage. Future versions can coexist.

### ADR-008: Caching Strategy
**Decision:** Redis with tagged caching
**Rationale:**
- Dashboard aggregations: Cache with 5-min TTL, invalidate on project status changes
- Document categories: Cache with 1-hour TTL
- Cache invalidation via Model Observers
- Cache keys pattern: `sipdok:{entity}:{identifier}`

### ADR-009: Queue Driver
**Decision:** Redis queue driver
**Rationale:** Already using Redis for cache, same infrastructure. Handles: file upload processing, notifications dispatch, export generation.

### ADR-010: File Storage
**Decision:** Laravel Storage with `local` disk (configurable to S3)
**Rationale:** Local for development/test, S3-compatible for production. Filesystem abstraction makes switching seamless.

## 3. Backend Patterns

### 3.1 Controller Pattern
```php
// Thin controller — delegates to Service
class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create($request->validated());
        return ApiResponse::created(new ProjectResource($project));
    }
}
```

### 3.2 Form Request Pattern
```php
// Validation + Authorization in Form Request
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('pemohon');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_category_id' => ['required', 'exists:document_categories,id'],
            // ...
        ];
    }
}
```

### 3.3 API Resource Pattern
```php
// Consistent API responses
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_code' => $this->project_code,
            'title' => $this->title,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'documents_count' => $this->whenCounted('documents'),
            // ...
        ];
    }
}
```

### 3.4 Enum Pattern
```php
enum ProjectStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Revised = 'revised';
    case Rejected = 'rejected';

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::Draft => in_array($newStatus, [self::Submitted]),
            self::Submitted => in_array($newStatus, [self::InReview]),
            self::InReview => in_array($newStatus, [self::Approved, self::Revised, self::Rejected]),
            self::Revised => in_array($newStatus, [self::Submitted]),
            default => false,
        };
    }
}
```

### 3.5 Policy Pattern
```php
class ProjectPolicy
{
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id
            && in_array($project->status, ['draft', 'revised']);
    }

    public function review(User $user, Project $project): bool
    {
        return $user->hasRole('penilai')
            && $project->status === 'in_review'
            && $project->current_reviewer_id === $user->id;
    }
}
```

## 4. Frontend Patterns

### 4.1 API Layer Pattern
```javascript
// api/client.js - Centralized Axios instance
import axios from 'axios'

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api/v1',
  withCredentials: true,
  headers: { 'Accept': 'application/json' }
})

// Request interceptor: CSRF token
// Response interceptor: 401 → redirect to login, toast errors
```

### 4.2 Composable Pattern
```javascript
// composables/useProjects.js
export function useProjects() {
  const projects = ref([])
  const loading = ref(false)
  const meta = ref({})

  async function fetchProjects(params = {}) {
    loading.value = true
    try {
      const response = await projectApi.list(params)
      projects.value = response.data.data
      meta.value = response.data.meta
    } finally {
      loading.value = false
    }
  }

  return { projects, loading, meta, fetchProjects }
}
```

### 4.3 Route Guard Pattern
```javascript
// Router guard for role-based access
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }
  if (to.meta.role && !authStore.hasRole(to.meta.role)) {
    return next({ name: 'dashboard' })
  }
  next()
})
```

## 5. Performance Optimization Strategy

### 5.1 Database Level
- Composite indexes on frequently queried column combinations
- Partial indexes for active records
- PostgreSQL EXPLAIN ANALYZE for query optimization
- Avoid N+1 with eager loading (with, withCount)
- Use cursor pagination for very large datasets (>10k records)

### 5.2 Application Level
- Redis caching for dashboard aggregations
- Queued jobs for heavy operations (uploads, exports, notifications)
- Chunk processing for seeders and exports
- API response time monitoring

### 5.3 Frontend Level
- Lazy loading routes (dynamic imports)
- Debounced search inputs
- Virtual scrolling for large lists (optional)
- Optimistic UI updates where appropriate
- Efficient re-rendering with computed properties

## 6. Security Measures

| Layer       | Measure                                          |
|-------------|--------------------------------------------------|
| Auth        | Sanctum SPA, CSRF token, HTTP-only cookies       |
| API         | Rate limiting, input validation, FormRequest      |
| Auth-z      | Spatie Permission + Laravel Policies              |
| Files       | MIME validation, size limit, storage outside webroot |
| Database    | Eloquent ORM (parameterized queries), constraints |
| Frontend    | Vue auto-escape, sanitized HTML, CORS config     |
| Headers     | X-Content-Type-Options, X-Frame-Options, etc.    |
