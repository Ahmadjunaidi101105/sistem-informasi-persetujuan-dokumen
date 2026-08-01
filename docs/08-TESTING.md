# Testing Plan — SIPDOK

## 1. Testing Stack
- **Backend:** PHPUnit + Pest PHP
- **Frontend:** Vitest (optional, prioritize backend tests)
- **Database:** SQLite in-memory for unit tests, PostgreSQL for feature tests

## 2. Test Database Setup
```php
// phpunit.xml
<env name="DB_CONNECTION" value="pgsql"/>
<env name="DB_DATABASE" value="sipdok_testing"/>
<env name="CACHE_DRIVER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
```

Use `RefreshDatabase` trait for all feature tests.

## 3. Unit Tests

### 3.1 ProjectStatus Enum Test
```
✓ can transition from draft to submitted
✓ can transition from submitted to in_review
✓ can transition from in_review to approved
✓ can transition from in_review to revised
✓ can transition from in_review to rejected
✓ can transition from revised to submitted
✓ cannot transition from draft to approved
✓ cannot transition from approved to any status
✓ cannot transition from rejected to any status
✓ cannot transition from submitted to approved (skip in_review)
```

### 3.2 Project Code Generation Test
```
✓ generates code in format PRJ-YYYY-NNNNN
✓ auto-increments sequential number
✓ resets sequence per year
✓ handles concurrent generation safely
```

### 3.3 Project Policy Test
```
✓ pemohon can view own project
✓ pemohon cannot view others project
✓ pemohon can update own draft project
✓ pemohon can update own revised project
✓ pemohon cannot update submitted project
✓ pemohon cannot update approved project
✓ pemohon can delete own draft project
✓ pemohon cannot delete submitted project
✓ penilai can view all non-draft projects
✓ penilai cannot view draft projects of others
✓ penilai can review project assigned to them
✓ penilai cannot review project assigned to another penilai
```

## 4. Feature Tests

### 4.1 Authentication Tests
```
✓ user can register as pemohon with valid data
✓ registration fails with duplicate email
✓ registration fails without company_name
✓ registration fails with weak password
✓ user can login with valid credentials
✓ login fails with wrong password
✓ login is rate limited after 5 attempts
✓ authenticated user can get profile
✓ user can logout
✓ unauthenticated requests return 401
```

### 4.2 Project CRUD Tests
```
✓ pemohon can create a new project
✓ project is created with draft status
✓ project_code is auto-generated
✓ pemohon can view own projects list
✓ pemohon cannot see others projects
✓ pemohon can view own project detail
✓ pemohon can update own draft project
✓ pemohon cannot update submitted project
✓ pemohon can delete own draft project
✓ pemohon cannot delete non-draft project
✓ penilai cannot create a project
✓ project list supports pagination
✓ project list supports filtering by status
✓ project list supports search
✓ project list supports sorting
```

### 4.3 Project Workflow Tests (Critical Path)
```
# Submit Flow
✓ pemohon can submit draft project with documents
✓ pemohon cannot submit draft project without documents
✓ pemohon cannot submit already submitted project
✓ submit changes status to submitted
✓ submit sets submitted_at timestamp
✓ submit creates review log entry

# Take Review Flow
✓ penilai can take submitted project for review
✓ penilai cannot take project already in review
✓ take-review changes status to in_review
✓ take-review sets current_reviewer_id

# Approve Flow
✓ current reviewer can approve project
✓ other penilai cannot approve (not current reviewer)
✓ approve changes status to approved
✓ approve sets approved_at timestamp
✓ approved project cannot be changed

# Revise Flow
✓ current reviewer can request revision with notes
✓ revision requires notes (min 10 chars)
✓ revise changes status to revised
✓ revise increments revision_count
✓ revise resets current_reviewer_id to null
✓ pemohon can resubmit revised project

# Reject Flow
✓ current reviewer can reject with notes
✓ rejection requires notes (min 10 chars)
✓ reject changes status to rejected
✓ reject sets rejected_at timestamp
✓ rejected project cannot be modified

# Full Cycle
✓ complete flow: draft → submit → review → approve
✓ revision cycle: draft → submit → review → revise → resubmit → review → approve
✓ rejection flow: draft → submit → review → reject
```

### 4.4 Document Upload Tests
```
✓ pemohon can upload PDF document to draft project
✓ pemohon can upload DOC/DOCX document
✓ pemohon can upload JPG/PNG image
✓ upload rejects invalid file types (exe, php, etc)
✓ upload rejects files over 10MB
✓ pemohon cannot upload to submitted project
✓ pemohon can upload to revised project
✓ penilai cannot upload documents
✓ pemohon can delete document from draft project
✓ pemohon can download own document
✓ penilai can download any document
```

### 4.5 Dashboard Tests
```
✓ pemohon dashboard returns correct summary counts
✓ pemohon dashboard shows only own data
✓ pemohon dashboard returns monthly trends
✓ penilai dashboard returns correct summary counts
✓ penilai dashboard includes all non-draft projects
✓ penilai dashboard returns approval rate
✓ penilai dashboard returns category distribution
✓ dashboard responses are cached
✓ cache is invalidated on status change
```

### 4.6 Notification Tests
```
✓ penilai receives notification when project submitted
✓ pemohon receives notification when project taken for review
✓ pemohon receives notification when project approved
✓ pemohon receives notification when revision requested
✓ pemohon receives notification when project rejected
✓ user can view notifications list
✓ user can mark notification as read
✓ user can mark all notifications as read
✓ unread count is correct
```

### 4.7 Export Tests
```
✓ can export projects list to Excel
✓ export respects filters
✓ can export single project to PDF
✓ export requires authentication
✓ pemohon only exports own projects
```

### 4.8 Authorization Tests
```
✓ pemohon cannot access penilai dashboard
✓ penilai cannot access pemohon-only actions
✓ pemohon cannot review projects
✓ penilai cannot submit projects
✓ unauthenticated user cannot access any API
```

## 5. Test Execution

### Run All Tests
```bash
php artisan test
# or
./vendor/bin/pest
```

### Run Specific Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run Specific Test
```bash
php artisan test --filter=ProjectWorkflowTest
```

### Coverage Report
```bash
php artisan test --coverage --min=70
```

## 6. Test Data Setup
- Use Factories for all test data
- Use `RefreshDatabase` trait
- Create helper methods for common setups:
  ```php
  private function createPemohon(): User
  private function createPenilai(): User
  private function createProjectWithDocuments(User $user, string $status = 'draft'): Project
  ```

## 7. Critical Test Scenarios (Must Pass)

| # | Scenario | Priority |
|---|----------|----------|
| 1 | Full approval flow (draft → submitted → in_review → approved) | P0 |
| 2 | Full revision flow with resubmit | P0 |
| 3 | Rejection flow | P0 |
| 4 | Invalid status transitions are blocked | P0 |
| 5 | Role-based access enforcement | P0 |
| 6 | Document upload validation | P1 |
| 7 | Dashboard data accuracy | P1 |
| 8 | Notification delivery | P1 |
| 9 | Pagination & filtering | P2 |
| 10 | Export functionality | P2 |
