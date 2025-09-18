
# ALLANACRUSIS_2.0 Modernization Roadmap

## Overview

This roadmap provides a phased, best-practice approach to modernizing the AllanaCrusis application for maintainability, scalability, and future integration (API, LLM, MCP, mobile, etc.). Each phase includes rationale, key actions, and practical examples.

---

## Phase 1: Database Foundation

**Why first?**  
A robust, normalized, and well-indexed database is the foundation for all future improvements.

**Actions:**
- Review and apply database structure improvements:
  - Use utf8mb4 for all tables/columns.
  - Add missing indexes, foreign keys, and audit columns (created_at, updated_at).
  - Normalize tables (e.g., split user roles into a join table).
  - Use INT/AUTO_INCREMENT for primary keys where possible.
  - Add TINYINT(1) for booleans, TEXT for long descriptions.
- Migrate data as needed (e.g., update charsets, split roles).
- Add automated database backups and migration scripts.

**Example:**
```sql
ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users MODIFY COLUMN roles VARCHAR(255) NULL;
-- Create user_roles table for many-to-many relationship
CREATE TABLE user_roles (
  user_id INT UNSIGNED,
  role_id INT UNSIGNED,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id_users),
  FOREIGN KEY (role_id) REFERENCES roles(id_role)
);
```

---

## Phase 2: Preparation & Testing

**Why now?**  
Testing and code audit ensure you can safely refactor and catch regressions early.

**Actions:**
- Audit all current includes, CRUD scripts, and their usage.
- Add automated tests for critical business logic and data access.
- Introduce PDO for new code, but keep mysqli for legacy scripts.

**Example:**
```php
// Use PDO for new DB access
$dsn = "mysql:host=...;dbname=...;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
```

---

## Phase 3: API & Architecture

**Why now?**  
A modern API unlocks integration, mobile, and LLM/MCP access.

**Actions:**
- Set up a basic router (or micro-framework) for new API endpoints.
- For each resource (e.g., instruments, parts), create a RESTful controller using PDO.
- Expose new endpoints (e.g., /api/v1/instruments) alongside existing scripts.

**Example Directory Structure:**
```
src/
  api/
    v1/
      index.php
      InstrumentController.php
      PartController.php
      ...
  includes/
    ... (legacy or shared code)
```

**Example Router:**
```php
// src/api/v1/index.php
if (preg_match('#^/api/v1/instruments(?:/(\\d+))?$#', $path, $matches)) {
  $controller = new InstrumentController();
  $id = $matches[1] ?? null;
  switch ($method) {
    case 'GET': echo $id ? $controller->show($id) : $controller->index(); break;
    case 'POST': echo $controller->store(); break;
    case 'PUT': case 'PATCH': echo $controller->update($id); break;
    case 'DELETE': echo $controller->destroy($id); break;
    default: http_response_code(405); echo json_encode(['error' => 'Method Not Allowed']);
  }
  exit;
}
```

---

## Phase 4: Incremental Migration

**Why incremental?**  
Minimizes risk and allows for continuous delivery.

**Actions:**
- Update frontend AJAX for one feature/page at a time to use the new API.
- Gradually migrate more features/pages to use the new API endpoints.
- Refactor business logic into reusable classes/services as you go.
- Remove or archive legacy scripts as their features are replaced.

**Example:**
- Migrate `instruments.php` to use `/api/v1/instruments` for all CRUD.
- Once stable, remove `fetch_instruments.php`, `insert_instruments.php`, etc.

---

## Phase 5: Modernization & Expansion

**Why last?**  
These steps add value once the core is modern and stable.

**Actions:**
- Remove old mysqli code and scripts.
- Add authentication (OAuth2/JWT), rate limiting, and documentation to the API.
- Optimize, test, and document the new architecture.
- Add support for webhooks, bulk export/import, and LLM/MCP-specific endpoints.
- Open up the API for mobile, LLM, or third-party integrations.

---

## Best Practices & Examples

- **API-First:** Use RESTful or GraphQL APIs for all data access.
- **Authentication:** Use OAuth2/JWT for secure, token-based access.
- **Documentation:** Use OpenAPI/Swagger for API docs.
- **Testing:** Add unit/integration tests for all endpoints.
- **Frontend:** Use fetch/axios and a modern JS framework for UI.
- **Accessibility:** Follow ARIA and i18n best practices.
- **Security:** Sanitize/validate all inputs, use HTTPS, audit dependencies.

---

## Sample Migration Plan

1. Inventory all includes and their usage.
2. Create `/api/v1/` and migrate one entity (e.g., instruments) as a test.
3. Update JS in `instruments.php` to use the new API endpoint and handle JSON.
4. Add token/session checks to API endpoints.
5. Repeat for other entities (parts, users, etc.).
6. Write API docs for each endpoint.
7. Remove/archive old include files after migration.

---

### Example: mysqli to PDO
```php
$f_link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

```php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();
- Replace all mysqli_connect/f_sqlConnect with PDO connection code.
- Use `$pdo->prepare()` and `$stmt->execute()` for queries with variables.


---

## 11. Example: RESTful API Structure for CRUD

**Best Practice:**
Group CRUD operations by resource/entity and use a centralized router/controller. This is more maintainable and scalable than one script per operation/table.

### Example Directory Structure

```
src/
  api/
  v1/
    index.php         # Main API entrypoint/router
    InstrumentController.php
    PartController.php
    UserController.php
    ...
  includes/
  ... (legacy or shared code)
```

### Example Router (index.php)
```php
// src/api/v1/index.php
require_once 'InstrumentController.php';
// ... other controllers

$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if (preg_match('#^/api/v1/instruments(?:/(\d+))?$#', $path, $matches)) {
  $controller = new InstrumentController();
  $id = $matches[1] ?? null;
  switch ($method) {
    case 'GET':
      echo $id ? $controller->show($id) : $controller->index();
      break;
    case 'POST':
      echo $controller->store();
      break;
    case 'PUT':
    case 'PATCH':
      echo $controller->update($id);
      break;
    case 'DELETE':
      echo $controller->destroy($id);
      break;
    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method Not Allowed']);
  }
  exit;
}
// ... more routes
```

### Example Controller (InstrumentController.php)
```php
class InstrumentController {
  public function index() {
    // Return all instruments as JSON
  }
  public function show($id) {
    // Return a single instrument as JSON
  }
  public function store() {
    // Create a new instrument from POST data
  }
  public function update($id) {
    // Update instrument with PUT/PATCH data
  }
  public function destroy($id) {
    // Delete instrument
  }
}
```

### Benefits
- Clean, RESTful URLs
- Centralized validation, authentication, and error handling
- Easy to extend for new resources or endpoints
- Ready for modern web/mobile/LLM integration

---

---

## 1. List of `src/includes` Files

- admin_fetch_users.php
- admin_insert_users.php
- admin_select_users.php
- delete_compositions.php
- delete_expired_tokens.php
- delete_partcollections.php
- delete_parts.php
- delete_playgrams.php
- delete_records.php
- download_part.php
- email_verification.php
- fetch_composition_parts.php
- fetch_compositions.php
- fetch_concerts.php
- fetch_ensembles.php
- fetch_genres.php
- fetch_instruments.php
- fetch_instruments_list.php
- fetch_login.php
- fetch_papersizes.php
- fetch_partcollections.php
- fetch_parts.php
- fetch_parts_data.php
- fetch_parttypes.php
- fetch_parttypes_list.php
- fetch_playgram_distribution.php
- fetch_playgram_items.php
- fetch_playgrams.php
- fetch_recordings.php
- fetch_reports.php
- fetch_section_instruments.php
- fetch_sections.php
- fetch_sections_list.php
- footer.php
- functions.php
- header.php
- insert_compositions.php
- insert_concerts.php
- insert_ensembles.php
- insert_genres.php
- insert_instrumentation.php
- insert_instruments.php
- insert_papersizes.php
- insert_partcollections.php
- insert_parts.php
- insert_parttypes.php
- insert_playgrams.php
- insert_recordings.php
- insert_section_instruments.php
- insert_sections.php
- navbar.php
- passwordLibClass.php
- password_hash.php
- password_reset.php
- report_missing_parts.php
- reset_password.php
- search_compositions.php
- select_composition_parts.php
- select_compositions.php
- select_concerts.php
- select_ensembles.php
- select_genres.php
- select_instruments.php
- select_papersizes.php
- select_partcollections.php
- select_parts.php
- select_parttypes.php
- select_playgrams.php
- select_recordings.php
- select_sections.php
- sound.php
- table2CSV.js
- update_enabled_status.php
- update_instruments_scoreorder.php
- update_playgramorder.php
- update_scoreorder.php
- upload_part.php
- upload_recording.php

---

## 2. Typical Usage Patterns

- Most files are included via `require_once` in main PHP pages (e.g., `instruments.php`, `parts.php`).
- They handle CRUD operations for various entities (instruments, parts, users, etc.).
- Data is often returned as HTML fragments or JSON, depending on the file.

---

## 3. Example Pages Using These Includes

- `src/instruments.php` uses:
  - fetch_instruments.php
  - insert_instruments.php
  - select_instruments.php
  - delete_parts.php (for deletion logic)
- `src/parts.php` uses:
  - fetch_parts.php
  - insert_parts.php
  - delete_parts.php
  - select_parts.php
- `src/index.php` uses:
  - fetch_compositions.php
  - fetch_recordings.php
- Many other pages use similar patterns for other entities.

---

## 4. Refactor Effort Estimate

### General Steps for Each File

1. **Convert to API Endpoint:**
   - Change output to JSON (not HTML).
   - Use RESTful conventions (GET, POST, PUT, DELETE).
   - Validate and sanitize all input.
   - Standardize error and success responses.

2. **Authentication:**
   - Add token-based authentication (e.g., JWT or session tokens).

3. **Update Frontend:**
   - Update AJAX calls to use new API endpoints and handle JSON responses.
   - Refactor any HTML fragment handling to use client-side rendering.

4. **Documentation:**
   - Document each endpoint (input, output, errors).

### Effort by File Type

| File Type                | Count | Effort per File | Notes |
|--------------------------|-------|-----------------|-------|
| fetch_* / select_*       | ~30   | 1-2h            | Convert to GET/POST, JSON output |
| insert_*                 | ~12   | 1-2h            | Convert to POST, JSON output |
| delete_*                 | ~8    | 1-2h            | Convert to DELETE, JSON output |
| update_*                 | ~4    | 1-2h            | Convert to PUT/PATCH, JSON output |
| Other (auth, utils, etc) | ~10   | 2-4h            | More complex, may require extra logic |

**Total Estimate:**  
- Simple CRUD includes: 1-2 hours each  
- Complex includes (auth, file upload, reporting): 2-4 hours each  
- Frontend refactor: 2-4 hours per main page  
- Documentation: 1 hour per endpoint

---

## 5. Integration Impact

- All main pages using AJAX or form POSTs to includes will need to be updated to use the new API endpoints.
- Mobile and external integrations will be much easier, as all data will be accessible via documented JSON APIs.
- Consider using a router or micro-framework (e.g., Slim, Lumen) for cleaner API structure.

---

## 6. Recommendations

- Start with the most-used entities (instruments, parts, users).
- Build a versioned API (e.g., `/api/v1/instruments`).
- Gradually migrate frontend to use the new API.
- Add automated tests for each endpoint.
- Document all endpoints for future integrators.

---

## 7. Sample API Refactor: `fetch_instruments.php`

**Current:**
- Accepts POST, returns HTML or JSON depending on input.
- Used by `instruments.php` for AJAX table and detail fetches.

**API Refactor Example:**
```php
// api/v1/instruments.php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id = $_GET['id_instrument'] ?? null;
    if ($id) {
        $sql = "SELECT * FROM instruments WHERE id_instrument = '" . mysqli_real_escape_string($f_link, $id) . "'";
        $res = mysqli_query($f_link, $sql);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        $sql = "SELECT id_instrument, name FROM instruments WHERE enabled = 1 ORDER BY collation;";
        $res = mysqli_query($f_link, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
    }
    mysqli_close($f_link);
    exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']);
```

---

## 8. Sample Migration Plan

1. **Inventory:** List all includes and their usage in main pages.
2. **API Layer:** Create `/api/v1/` directory and migrate one entity (e.g., instruments) as a test.
3. **Frontend Update:** Update JS in `instruments.php` to use the new API endpoint and handle JSON.
4. **Authentication:** Add token/session checks to API endpoints.
5. **Iterate:** Repeat for other entities (parts, users, etc.).
6. **Documentation:** Write API docs for each endpoint.
7. **Deprecate:** Remove or archive old include files after migration.

---
