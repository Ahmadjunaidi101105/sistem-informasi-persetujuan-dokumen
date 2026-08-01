# Coding Conventions & Standards — SIPDOK

## 1. PHP / Laravel Conventions

### Naming
| What | Convention | Example |
|------|-----------|---------|
| Controller | PascalCase, singular | `ProjectController` |
| Model | PascalCase, singular | `Project` |
| Migration | snake_case, descriptive | `create_projects_table` |
| Seeder | PascalCase + Seeder | `ProjectSeeder` |
| Factory | PascalCase + Factory | `ProjectFactory` |
| Form Request | PascalCase + Request | `StoreProjectRequest` |
| API Resource | PascalCase + Resource | `ProjectResource` |
| Service | PascalCase + Service | `ProjectService` |
| Policy | PascalCase + Policy | `ProjectPolicy` |
| Notification | PascalCase + Notification | `ProjectApprovedNotification` |
| Job | PascalCase + Job | `ProcessDocumentUploadJob` |
| Event | PascalCase, past tense | `ProjectSubmitted` |
| Enum | PascalCase | `ProjectStatus` |
| Trait | PascalCase, adjective | `HasProjectCode` |
| Test | PascalCase + Test | `ProjectWorkflowTest` |
| DB columns | snake_case | `created_at`, `user_id` |
| Routes | kebab-case | `take-review`, `read-all` |
| Config keys | snake_case | `sipdok.max_file_size` |

### PSR-12 Rules
- 4 spaces indentation (no tabs)
- Opening braces on same line for classes/methods
- One blank line before return statements
- Strict types declaration: `declare(strict_types=1);`
- Type declarations for all parameters and return types
- Use readonly properties where applicable (PHP 8.2+)
- Use constructor property promotion

### Laravel Best Practices
```php
// ✅ DO: Thin controller, fat service
public function store(StoreProjectRequest $request): JsonResponse
{
    $project = $this->projectService->create($request->validated());
    return ApiResponse::created(new ProjectResource($project));
}

// ❌ DON'T: Business logic in controller
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([...]);
    $project = new Project();
    $project->fill($validated);
    $project->status = 'draft';
    $project->project_code = ...;
    $project->save();
    // ...
}
```

```php
// ✅ DO: Eager loading
Project::with(['user', 'documentCategory', 'documents'])->paginate(15);

// ❌ DON'T: Lazy loading in loops (N+1)
$projects = Project::paginate(15);
foreach ($projects as $project) {
    echo $project->user->name; // N+1!
}
```

```php
// ✅ DO: Use Enums
$project->status = ProjectStatus::Draft;

// ❌ DON'T: String literals
$project->status = 'draft';
```

```php
// ✅ DO: Use Form Request for validation
class StoreProjectRequest extends FormRequest { ... }

// ❌ DON'T: Validate in controller
$request->validate([...]);
```

```php
// ✅ DO: Use API Resources
return new ProjectResource($project);

// ❌ DON'T: Return raw model
return response()->json($project);
```

## 2. Vue 3 / Frontend Conventions

### File Naming
| What | Convention | Example |
|------|-----------|---------|
| Components | PascalCase.vue | `StatusBadge.vue` |
| Pages | PascalCase + Page.vue | `DashboardPage.vue` |
| Layouts | PascalCase + Layout.vue | `DashboardLayout.vue` |
| Composables | camelCase, use prefix | `useProjects.js` |
| Stores | camelCase .js | `auth.js` |
| API modules | camelCase .js | `projects.js` |
| Utils | camelCase .js | `formatters.js` |

### Component Structure (Composition API)
```vue
<script setup>
// 1. Imports
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// 2. Props & Emits
const props = defineProps({
  projectId: { type: Number, required: true }
})
const emit = defineEmits(['updated'])

// 3. Composables & Stores
const router = useRouter()

// 4. Reactive state
const loading = ref(false)
const project = ref(null)

// 5. Computed
const isEditable = computed(() => 
  ['draft', 'revised'].includes(project.value?.status)
)

// 6. Methods
async function fetchProject() { ... }
function handleSubmit() { ... }

// 7. Lifecycle
onMounted(() => fetchProject())
</script>

<template>
  <!-- Template here -->
</template>

<style scoped>
/* Scoped styles only when needed (prefer Tailwind) */
</style>
```

### Vue Best Practices
```javascript
// ✅ DO: Use composables for reusable logic
export function useProjects() {
  const projects = ref([])
  const loading = ref(false)
  // ...
  return { projects, loading, fetchProjects }
}

// ❌ DON'T: Duplicate logic across components
```

```javascript
// ✅ DO: Use Pinia for shared state
export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isAuthenticated = computed(() => !!user.value)
  // ...
})

// ❌ DON'T: Use event bus or provide/inject for app state
```

```html
<!-- ✅ DO: Use v-if for conditional rendering -->
<LoadingSpinner v-if="loading" />
<DataTable v-else :data="projects" />

<!-- ❌ DON'T: Use v-show for heavy components -->
```

## 3. API Response Standards

### Success Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": { ... }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## 4. Git Conventions

### Commit Message Format
```
<type>: <short description>

Types:
  feat:     New feature
  fix:      Bug fix
  refactor: Code refactoring
  test:     Adding tests
  docs:     Documentation
  chore:    Maintenance tasks
  style:    Code style/formatting
  perf:     Performance improvement

Examples:
  feat: implement project submission workflow
  fix: resolve N+1 query in project listing
  refactor: extract review logic to ReviewService
  test: add feature tests for approval flow
  docs: add API documentation for export endpoints
  chore: configure Docker services
  perf: add Redis caching to dashboard endpoint
```

### Branch Naming
```
feature/database-models
feature/auth-api
feature/project-workflow
feature/frontend-dashboard
fix/project-status-validation
refactor/service-layer
```

## 5. Database Conventions

- Table names: plural, snake_case (`projects`, `project_reviews`)
- Column names: snake_case (`created_at`, `user_id`)
- Foreign keys: `{singular_table}_id` (`user_id`, `project_id`)
- Boolean columns: `is_` prefix (`is_active`)
- Timestamps: always include `created_at` and `updated_at`
- Soft deletes: only if specifically needed (not in this project)
- Index names: `idx_{table}_{columns}` (`idx_projects_status`)

## 6. File & Folder Organization

### Backend Route Organization
```php
// routes/api.php — organized by feature
Route::prefix('v1')->group(function () {
    // Auth (public)
    Route::prefix('auth')->group(function () { ... });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::prefix('dashboard')->group(function () { ... });

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::prefix('projects/{project}')->group(function () {
            Route::post('submit', [ProjectController::class, 'submit']);
            // ...
        });

        // Notifications
        Route::prefix('notifications')->group(function () { ... });

        // Export
        Route::prefix('export')->group(function () { ... });
    });
});
```

## 7. Error Handling

### Backend
- Use Laravel's exception handler for consistent error responses
- Create custom exceptions for business logic errors
- Never expose internal errors to API responses in production
- Log all errors with context

### Frontend
- Axios interceptor catches 401 → redirect to login
- Axios interceptor catches 422 → show validation errors inline
- Axios interceptor catches 500 → show generic error toast
- Always show loading states
- Always handle empty states
