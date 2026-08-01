# REST API Documentation — SIPDOK

## Base URL
```
/api/v1
```

## Authentication
All endpoints (except login/register) require Sanctum SPA authentication.
- Cookie-based session authentication for SPA
- CSRF token via `/sanctum/csrf-cookie`

## Response Format Standard
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

## Error Response Format
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## 1. Authentication Endpoints

### POST `/auth/register`
Register new Pemohon user.
```
Body:
  name: string, required, max:255
  email: string, required, email, unique:users
  password: string, required, min:8, confirmed
  password_confirmation: string, required
  phone: string, nullable, max:20
  company_name: string, required, max:255
  company_address: string, required

Response: 201
  { success: true, data: { user, token } }
```

### POST `/auth/login`
```
Body:
  email: string, required
  password: string, required

Response: 200
  { success: true, data: { user: { ...user, roles, permissions }, token } }

Rate Limit: 5 attempts/minute
```

### POST `/auth/logout`
```
Headers: Authorization (Sanctum)
Response: 200
  { success: true, message: "Logged out successfully" }
```

### GET `/auth/user`
Get authenticated user profile with roles & permissions.
```
Response: 200
  { success: true, data: { user: { ...user, roles, permissions, unread_notifications_count } } }
```

---

## 2. Dashboard Endpoints

### GET `/dashboard/pemohon`
Dashboard data for Pemohon role.
```
Response: 200
  {
    data: {
      summary: {
        total_projects: 25,
        by_status: { draft: 3, submitted: 5, in_review: 2, approved: 10, revised: 3, rejected: 2 },
        pending_revisions: 3
      },
      recent_projects: [ ...last 5 projects ],
      monthly_submissions: [ { month: "2025-01", count: 5 }, ... ]
    }
  }

Cache: Redis, TTL 5 minutes, key: dashboard:pemohon:{user_id}
```

### GET `/dashboard/penilai`
Dashboard data for Penilai role.
```
Response: 200
  {
    data: {
      summary: {
        total_submissions: 500,
        by_status: { submitted: 50, in_review: 20, approved: 300, revised: 80, rejected: 50 },
        pending_review: 50,
        my_reviewed: 120
      },
      approval_rate: 75.5,
      monthly_trends: [ { month: "2025-01", approved: 30, revised: 10, rejected: 5 }, ... ],
      category_distribution: [ { category: "AMDAL", count: 50 }, ... ],
      recent_reviews: [ ...last 10 reviews ]
    }
  }

Cache: Redis, TTL 5 minutes, key: dashboard:penilai:{user_id}
```

---

## 3. Project Endpoints

### GET `/projects`
List projects with filtering, sorting, pagination.
```
Query Params:
  page: int, default 1
  per_page: int, default 15, max 100
  status: string (draft|submitted|in_review|approved|revised|rejected)
  category_id: int
  search: string (searches title, project_code, description)
  sort_by: string (created_at|submitted_at|updated_at|project_code|title), default created_at
  sort_order: string (asc|desc), default desc
  date_from: date (Y-m-d)
  date_to: date (Y-m-d)

Response: 200
  {
    data: [ { id, project_code, title, status, category: { id, name }, user: { id, name, company_name }, documents_count, reviews_count, submitted_at, created_at } ],
    meta: { current_page, last_page, per_page, total }
  }

Notes:
  - Pemohon: otomatis filter user_id = auth user (hanya lihat milik sendiri)
  - Penilai: bisa lihat semua project yang status != draft
  - Eager load: user, documentCategory
  - Response time target: < 200ms
```

### POST `/projects`
Create new project (Pemohon only).
```
Body:
  title: string, required, max:255
  document_category_id: int, required, exists:document_categories,id
  description: text, nullable
  priority: string, nullable (low|normal|high), default normal
  notes: text, nullable

Response: 201
  { success: true, data: { project } }

Auto: status = 'draft', user_id = auth user, project_code = auto-generate
```

### GET `/projects/{id}`
Get project detail with relations.
```
Response: 200
  {
    data: {
      id, project_code, title, description, status, priority, notes,
      submitted_at, reviewed_at, approved_at, rejected_at, revision_count,
      user: { id, name, email, company_name, company_address },
      document_category: { id, name, code },
      current_reviewer: { id, name } | null,
      documents: [ { id, original_name, file_size, mime_type, version, created_at } ],
      reviews: [ { id, reviewer: { id, name }, status_from, status_to, notes, reviewed_at } ],
      created_at, updated_at
    }
  }

Authorization:
  - Pemohon: hanya project sendiri
  - Penilai: semua project (kecuali draft milik orang lain)
```

### PUT `/projects/{id}`
Update project (Pemohon only, status must be Draft or Revised).
```
Body:
  title: string, max:255
  document_category_id: int, exists:document_categories,id
  description: text, nullable
  priority: string (low|normal|high)
  notes: text, nullable

Response: 200
  { success: true, data: { project } }

Validation: status must be 'draft' or 'revised'
```

### DELETE `/projects/{id}`
Delete project (Pemohon only, status must be Draft).
```
Response: 200
  { success: true, message: "Project deleted" }

Validation: status must be 'draft'
Cascade: deletes related documents
```

---

## 4. Project Status Transition Endpoints

### POST `/projects/{id}/submit`
Submit project for review (Pemohon).
```
Validation:
  - Status must be 'draft' or 'revised'
  - Must have at least 1 document uploaded
  - All required fields filled

Response: 200
  { success: true, data: { project }, message: "Permohonan berhasil dikirim" }

Side effects:
  - status → 'submitted'
  - submitted_at = now()
  - Create review log entry
  - Dispatch notification to all Penilai (queued)
  - Invalidate dashboard cache
```

### POST `/projects/{id}/take-review`
Take project for review (Penilai).
```
Validation:
  - Status must be 'submitted'
  - current_reviewer_id must be null

Response: 200
  { success: true, data: { project } }

Side effects:
  - status → 'in_review'
  - current_reviewer_id = auth user
  - reviewed_at = now()
  - Create review log entry
  - Notify Pemohon (queued)
  - Invalidate dashboard cache
```

### POST `/projects/{id}/approve`
Approve project (Penilai).
```
Body:
  notes: text, nullable

Validation:
  - Status must be 'in_review'
  - current_reviewer_id must be auth user

Response: 200
  { success: true, data: { project }, message: "Permohonan disetujui" }

Side effects:
  - status → 'approved'
  - approved_at = now()
  - Create review log entry
  - Notify Pemohon (queued)
  - Invalidate dashboard cache
```

### POST `/projects/{id}/revise`
Request revision (Penilai).
```
Body:
  notes: text, required, min:10 (catatan revisi wajib diisi)

Validation:
  - Status must be 'in_review'
  - current_reviewer_id must be auth user

Response: 200
  { success: true, data: { project }, message: "Permintaan revisi dikirim" }

Side effects:
  - status → 'revised'
  - revision_count += 1
  - current_reviewer_id = null
  - Create review log entry
  - Notify Pemohon (queued)
  - Invalidate dashboard cache
```

### POST `/projects/{id}/reject`
Reject project (Penilai).
```
Body:
  notes: text, required, min:10 (alasan penolakan wajib diisi)

Validation:
  - Status must be 'in_review'
  - current_reviewer_id must be auth user

Response: 200
  { success: true, data: { project }, message: "Permohonan ditolak" }

Side effects:
  - status → 'rejected'
  - rejected_at = now()
  - Create review log entry
  - Notify Pemohon (queued)
  - Invalidate dashboard cache
```

---

## 5. Document Upload Endpoints

### POST `/projects/{id}/documents`
Upload document to project (Pemohon).
```
Body: multipart/form-data
  document: file, required
    - mimes: pdf,doc,docx,jpg,jpeg,png
    - max: 10240 (10MB)

Response: 201
  { success: true, data: { document } }

Validation:
  - Project status must be 'draft' or 'revised'
  - Project must belong to auth user
  - File validation (type, size)

Processing: Queued (dispatch UploadDocument job)
```

### GET `/projects/{id}/documents`
List documents for a project.
```
Response: 200
  { data: [ { id, original_name, file_size, mime_type, version, uploaded_by, created_at } ] }
```

### GET `/documents/{id}/download`
Download a document file.
```
Response: File download (binary)
Authorization: Owner or Penilai
```

### DELETE `/documents/{id}`
Delete a document (Pemohon, project must be draft/revised).
```
Response: 200
  { success: true, message: "Document deleted" }
```

---

## 6. Review / History Endpoints

### GET `/projects/{id}/reviews`
Get review history for a project.
```
Response: 200
  {
    data: [
      {
        id, reviewer: { id, name }, status_from, status_to,
        notes, reviewed_at
      }
    ]
  }
```

### GET `/reviews`
Get all review history (Penilai only).
```
Query Params:
  page, per_page, sort_by, sort_order
  reviewer_id: int (filter by specific reviewer)
  status_to: string (filter by decision type)
  date_from, date_to: date

Response: 200
  {
    data: [ { id, project: { id, project_code, title }, reviewer: { id, name }, status_from, status_to, notes, reviewed_at } ],
    meta: { ... }
  }
```

---

## 7. Notification Endpoints

### GET `/notifications`
Get user notifications.
```
Query Params:
  page: int, default 1
  per_page: int, default 15
  unread_only: boolean, default false

Response: 200
  {
    data: [ { id, type, data: { title, message, project_id, project_code }, read_at, created_at } ],
    meta: { ... },
    unread_count: 5
  }
```

### POST `/notifications/{id}/read`
Mark notification as read.
```
Response: 200
  { success: true }
```

### POST `/notifications/read-all`
Mark all notifications as read.
```
Response: 200
  { success: true, message: "All notifications marked as read" }
```

---

## 8. Export Endpoints

### GET `/export/projects`
Export projects to Excel.
```
Query Params: (same filters as GET /projects)
  format: string (xlsx|pdf), default xlsx

Response: File download

Processing: Queued for large datasets
Headers: Content-Disposition: attachment
```

### GET `/export/projects/{id}/pdf`
Export single project detail to PDF.
```
Response: PDF file download
```

---

## 9. Master Data Endpoints

### GET `/document-categories`
List all active document categories.
```
Response: 200
  { data: [ { id, name, code, description } ] }

Cache: Redis, TTL 1 hour
```

### GET `/users/pemohon`
List Pemohon users (Penilai only, for reference).
```
Query Params: search, page, per_page
Response: 200
  { data: [ { id, name, email, company_name } ], meta: { ... } }
```

---

## 10. API Rate Limiting

| Endpoint Group    | Limit              |
|-------------------|--------------------|
| Auth (login)      | 5 requests/minute  |
| Auth (register)   | 3 requests/minute  |
| General API       | 60 requests/minute |
| Export            | 5 requests/minute  |
| File Upload       | 10 requests/minute |

## 11. HTTP Status Codes Used

| Code | Usage                                     |
|------|-------------------------------------------|
| 200  | Success                                   |
| 201  | Created                                   |
| 204  | No Content (successful delete)            |
| 400  | Bad Request                               |
| 401  | Unauthenticated                           |
| 403  | Forbidden (no permission)                 |
| 404  | Not Found                                 |
| 422  | Validation Error                          |
| 429  | Too Many Requests (rate limited)          |
| 500  | Internal Server Error                     |
