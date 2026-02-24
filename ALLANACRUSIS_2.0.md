
# ALLANACRUSIS_2.0 Modernization Roadmap

## Overview

This roadmap provides a phased, best-practice approach to modernizing the AllanaCrusis application for maintainability, scalability, and future integration (API, LLM, MCP, mobile, etc.). Each phase includes rationale, key actions, and practical examples.

### Strategic Outcome for 2.0

By the end of this roadmap, AllanaCrusis should support:

- A stable, versioned API layer over core library data
- A role-aware MCP server for natural-language questions about the music library
- Explainable insights for search, playgram readiness, and data quality
- A safe path from read-only AI insights to optional workflow automation

### Priority User Questions to Enable

- "What grade 3-4 works do we have under 7 minutes with complete parts?"
- "What in upcoming playgrams is missing originals or PDFs?"
- "Suggest a balanced 40-minute program for our ensemble."
- "Which compositions have metadata gaps that hurt discoverability?"

---

## Phase 0: Data Onboarding & Migration (Adoption Critical)

**Why this comes first?**  
Most community ensembles start with spreadsheets/Google Sheets. If migration is hard, adoption stalls regardless of later AI features.

**Primary objective:**
- Get new organizations from “messy spreadsheet” to “searchable library with usable metadata” in hours, not weeks.

**Mechanism to implement:**

1. **Template-first onboarding package**
  - Provide canonical CSV templates for:
    - compositions (required, first-class path)
    - composition notes / librarian notes (required if available)
    - parts (optional advanced template)
    - part gap notes (optional: free-text such as "missing 2nd Trumpet")
  - Include required columns, accepted values, and examples in row 2.

2. **Two-pass import workflow**
  - **Pass A (quick start supporting data):** preload standard genres, ensembles, instruments, part types, and paper sizes.
  - **Pass B (library data):** compositions first; parts optional as a second step.
  - Prevents foreign-key and lookup failures during initial migration.

2.5 **Quick Start starter (recommended default)**
  - Offer a one-click "Community Band/Orchestra starter" pack with sensible defaults for supporting data.
  - Allow optional edits after preload, but avoid blocking composition import on full customization.
  - Use this starter to guide mapping and validation during composition import.

3. **Preview + dry-run validator**
  - Upload CSV (or Google Sheet export) and run validation before writing data.
  - Return row-level errors/warnings:
    - missing required fields
    - invalid enum/value mappings
    - duplicate catalog numbers
    - unresolved references (genre/ensemble/part type/paper size)
  - Provide downloadable “fix report” CSV.

4. **Column mapping UI + saved mapping profiles**
  - Let users map source columns (e.g., "Piece Title") to system fields (`name`).
  - Save org-specific mapping profiles to reuse for future imports.

5. **Normalization and deduplication assist**
  - Suggest canonical forms for composer/arranger names.
  - Flag likely duplicates using rules (`catalog_number`, normalized title+composer).
  - Allow merge/skip decisions during import review.
  - Parse common notes patterns (e.g., "missing 2nd Trumpet") into structured part-gap flags for later remediation.
  - Back this with alias/review tables (see `COMPOSER_NORMALIZATION.sql` Phase A) so normalization is non-breaking and repeatable.

6. **Google Sheets-friendly path**
  - Support import from:
    - downloaded CSV files
    - published CSV URLs from Google Sheets (read-only)
  - Keep first implementation simple: poll CSV URL on demand, no OAuth required.

7. **Chunked/background import for large libraries**
  - Reuse chunked-upload patterns for large CSV files.
  - Process in batches with resumable jobs and progress indicators.

8. **Post-import QA report**
  - Immediately produce a migration summary:
    - inserted/updated/skipped/error counts
    - missing metadata hotspots
    - compositions imported without structured part inventories (expected for many orgs)
    - inferred part gaps from notes (e.g., missing 2nd Trumpet)
    - parts without files

**Reinforcement for AI/MCP outcome:**
- Better ingestion quality directly improves search relevance, recommendations, and natural-language answer reliability.
- Mapping profiles + validation outputs become training examples for future AI-assisted import.

**Success criteria for Phase 0:**
- A first-time librarian can import at least 70-80% of composition-level legacy spreadsheet data in one session, even without structured part inventories.
- Validation errors are actionable and row-specific.
- Imported data is immediately usable in search, reports, and playgram workflows.

**Implementation runbook:**
- See `IMPORT_ONBOARDING_FLOW.md` for the exact import → review queue → alias approval → canonical update SQL sequence.
- See `CSV_TEMPLATE_PACK_SPEC.md` for template headers, field rules, validation codes, and sample rows.

---

## Phase 1: Database Foundation

**Why first?**  
A robust, normalized, and indexed data model is the foundation for reliable insights and APIs.

**Actions:**
- Apply schema hardening:
  - Use utf8mb4 for all tables/columns.
  - Add missing indexes, foreign keys, and audit columns (`created_at`, `updated_at`).
  - Normalize role storage (move from string roles toward join-table model).
  - Use `INT/AUTO_INCREMENT` primary keys where practical.
  - Use `TINYINT(1)` for booleans and `TEXT` for long descriptions.
- Add migration scripts and automated backups.
- Add analytics-ready indexes for frequent insight queries:
  - `compositions(enabled, grade, duration, genre, ensemble)`
  - `parts(catalog_number, id_part_type, originals_count)`
  - `playgrams(performance_date, enabled)` and `playgram_items(id_playgram, comp_order)`
- Publish a lightweight data dictionary for core entities and join paths.

**Reinforcement for AI/MCP outcome:**
- Treat this phase as the quality contract for natural-language answers.
- Add data-quality checks for null/invalid values in planning-critical fields (`grade`, `duration`, `genre`, `ensemble`, `enabled`).

---

## Phase 2: Preparation & Testing

**Why now?**  
Testing and code audit reduce regression risk before architectural migration.

**Actions:**
- Audit all include scripts and CRUD usage patterns.
- Add automated tests for critical business logic and data-access paths.
- Introduce PDO for new code while keeping mysqli for legacy paths during transition.
- Add baseline tests for AI-critical queries:
  - Search correctness and bounds
  - Readiness/risk report correctness
  - Program-duration aggregation correctness
- Create a small fixture database for deterministic insight test cases.

**Reinforcement for AI/MCP outcome:**
- Require repeatable test coverage for each new insight endpoint/tool query.
- Add regression checks for role access (`administrator`, `librarian`, `user`).

---

## Phase 3: API & Architecture

**Why now?**  
A clean API unlocks integration, mobile, and MCP tooling.

**Actions:**
- Establish `/api/v1/` with shared middleware and routing.
- Create RESTful controllers per resource domain using PDO.
- Expose API endpoints alongside legacy include scripts during migration.
- Standardize response envelope (`success`, `data`, `error`, `meta`).
- Implement role-aware middleware reusable by API and MCP integration layer.
- Add insight-oriented endpoints:
  - `/api/v1/insights/search`
  - `/api/v1/insights/playgram-readiness`
  - `/api/v1/insights/library-gaps`

**Reinforcement for AI/MCP outcome:**
- Keep insight endpoints deterministic and parameterized.
- Include explainability in response payloads (applied filters, counts, rationale).

---

## Phase 4: Incremental Migration

**Why incremental?**  
Minimizes delivery risk while shipping value continuously.

**Actions:**
- Migrate one page/workflow at a time from include-driven AJAX to API calls.
- Refactor shared query/business logic into service methods.
- Remove/archive legacy scripts once replacement paths are stable.
- Prioritize AI-value migration order:
  1. `search` workflows
  2. `reports` and readiness workflows
  3. `playgram_builder` candidate/duration workflows

**Reinforcement for AI/MCP outcome:**
- UI and MCP must consume the same service-backed query logic to avoid drift.

---

## Phase 5: Modernization & Expansion

**Why last?**  
These capabilities pay off after architecture is stable.

**Actions:**
- Complete mysqli retirement for migrated domains.
- Add authentication hardening (OAuth2/JWT where needed), rate limiting, and API docs.
- Expand support for webhooks and bulk export/import.
- Add observability for insight behavior:
  - Query latency and timeout rates
  - Row count distributions
  - Most common natural-language intents
  - Error categories (permission, validation, data gaps)

**Reinforcement for AI/MCP outcome:**
- Progress from read-only insight tools to optional assisted actions with explicit confirmations.

---

## Phase 6: MCP Delivery Track (Natural-Language Insights)

**Why this phase?**  
Operationalizes AI outcomes using hardened API and data layers from Phases 1-5.

**MVP scope (read-only first):**
- Implement role-aware deterministic MCP tools:
  - `search_compositions_nl`
  - `playgram_readiness_report`
  - `library_gap_analysis`
- Ensure each tool returns:
  - concise answer text
  - structured evidence rows
  - explainability metadata (filters, assumptions)

**Implementation checklist:**
1. Create MCP server project and secure DB/API connectivity.
2. Enforce input validation, query bounds, and execution timeouts.
3. Map app roles to tool-level permissions.
4. Add fixture-backed deterministic tests.
5. Publish usage examples for librarians and directors.

**Phase 6.5 (next tools):**
- Add `recommend_program_candidates` for duration/grade/genre-balanced suggestions.
- Add metadata normalization suggestion tools.

**Security guardrails:**
- Read-only by default.
- Parameterized queries only.
- Enforce row limits and timeout limits.
- Redact sensitive file-path details for non-privileged users.

---

## Outcome-Driven Milestones

### Milestone A: API Readiness
- Versioned API is stable for search, reports, playgrams, and parts.
- Shared response envelope and role checks are in place.

### Milestone B: Insight Readiness
- Deterministic insight endpoints produce explainable outputs.
- Baseline query tests pass for search/readiness/gap analysis.

### Milestone C: MCP MVP Live
- Users can ask natural-language questions and receive evidence-backed answers.
- Librarians can triage readiness risks directly from MCP results.

### Milestone D: Planning Assistant
- Program candidate recommendations are available with explicit rationale.

---

## Best Practices

- **API-first:** centralize all data access behind versioned APIs/services.
- **Security-first:** validate/sanitize inputs and enforce least privilege.
- **Explainability-first:** every insight should include enough evidence to verify.
- **Testing-first:** add unit/integration tests for core endpoints and tool paths.
- **Documentation-first:** maintain OpenAPI specs and MCP tool contracts.

---

## Consolidated Migration Plan

1. Inventory and classify includes by domain (`search`, `reports`, `playgrams`, `parts`, `recordings`, admin).
2. Create `/api/v1/` foundation with middleware (auth, role checks, validation, envelope).
3. Migrate one CRUD domain first (recommended: instruments).
4. Migrate AI-critical read domains next (search, readiness, playgram views).
5. Move SQL into service methods consumed by both UI and MCP.
6. Add insight endpoints (`/insights/search`, `/insights/playgram-readiness`, `/insights/library-gaps`).
7. Launch read-only MCP tools on these endpoints.
8. Add fixture-backed tests for correctness, role access, and explainability fields.
9. Add observability dashboard for usage and failures.
10. Expand to recommendation tools after quality/security gates pass.

---

## Refactor Effort Estimate

### Workstream Estimates

| Workstream | Scope | Typical Effort |
|---|---|---|
| API foundation | Router, middleware, response envelope, auth hooks | 1-2 weeks |
| CRUD migration | `fetch_*` / `select_*` / `insert_*` / `delete_*` / `update_*` | 1-2h per simple endpoint; 2-4h for complex endpoints |
| Frontend migration | Replace include-driven AJAX with API client calls | 2-4h per major page |
| Insight endpoints | Search/readiness/gaps deterministic read endpoints | 1-2 weeks |
| MCP MVP | 3 read-only tools + tests + guardrails | 2-3 weeks |
| Documentation | OpenAPI + prompt cookbook + runbooks | Ongoing, 1h+ per endpoint/tool |

### Total Program Estimate (sequential baseline)

- **Core API modernization:** ~4-7 weeks
- **MCP insight MVP:** +2-3 weeks
- **Planning-assistant extension:** +2-4 weeks

Parallel work can reduce calendar time.

---

## Example Modernization Snippets

### Example: `mysqli` to PDO

```php
// Legacy
$f_link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Modern
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->prepare("SELECT * FROM instruments WHERE id_instrument = :id");
$stmt->execute(['id' => $id]);
$row = $stmt->fetch();
```

### Example: RESTful Router Pattern

```php
// src/api/v1/index.php
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
      echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
  }
  exit;
}
```

---

## Appendix: Include-Layer Reality (Summary)

- The legacy include layer is broad and mostly CRUD-shaped (`fetch_*`, `select_*`, `insert_*`, `delete_*`, `update_*`).
- Key pages (`search`, `parts`, `playgram_builder`, `reports`, `recordings`) already contain logic that should be promoted into shared API services.
- Migrate domain-by-domain (not file-by-file) so user-visible AI/MCP value arrives earlier.

---
