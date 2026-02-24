# Import Onboarding Flow (Composition-First)

This runbook defines a practical sequence for importing legacy spreadsheet data and normalizing composer/arranger names without breaking the current schema.

## Goal

Get organizations from spreadsheet data to usable library search/reporting quickly, even when part inventories are incomplete.

## Scope

- Composition-first migration (required)
- Parts migration (optional, later)
- Composer/arranger normalization using alias/review workflow in COMPOSER_NORMALIZATION.sql (Phase A)
- CSV structures and validation contract defined in `CSV_TEMPLATE_PACK_SPEC.md`

## Prerequisites

1. Supporting data loaded (quick-start pack recommended):
   - genres
   - ensembles
   - instruments
   - part types
   - paper sizes
2. Composition CSV prepared with required fields.
3. Database backup completed.

## End-to-End Sequence

### Step 1: Initialize normalization support (one-time)

Run Phase A objects from COMPOSER_NORMALIZATION.sql:

- name_aliases
- name_normalization_review
- v_name_normalization_health

This step is non-breaking and safe to apply before imports.

### Step 2: Import composition data (primary ingest)

Import composition rows first (catalog, title, composer/arranger text, grade, genre, ensemble, duration, notes).

Notes:
- Do not block import if parts are unavailable.
- Preserve raw composer/arranger values as received; normalization is handled in later steps.

### Step 3: Queue unknown names for review

Execute the review-queue queries from COMPOSER_NORMALIZATION.sql:

- Operational query A (composer queue)
- Operational query B (arranger queue)

This populates name_normalization_review with unresolved names and usage counts.

### Step 4: Review and approve aliases

Work through pending review items and map raw variants to canonical values.

Typical canonical format:
- Last, First Middle

Approval actions:
1. Insert/update name_aliases rows for approved mappings.
2. Mark reviewed rows in name_normalization_review as approved or resolved.

### Step 5: Apply canonical updates

Execute Operational query C from COMPOSER_NORMALIZATION.sql to update compositions:

- normalize compositions.composer via name_aliases
- normalize compositions.arranger via name_aliases

Recommended execution pattern:
1. Run in a transaction on staging first.
2. Validate result counts.
3. Apply to production.

### Step 6: Re-queue and iterate

Re-run Step 3 after each import batch to identify newly seen variants.

Normalization becomes progressively better as alias mappings grow.

### Step 7: Post-import QA checks

Run checks after each batch:

1. Health view:
   - SELECT * FROM v_name_normalization_health;
2. Top pending names:
   - SELECT entity_type, raw_name, occurrence_count
     FROM name_normalization_review
     WHERE status = 'pending'
     ORDER BY occurrence_count DESC, raw_name ASC;
3. Composition usability checks:
   - missing required metadata counts
   - duplicate catalog_number checks
   - compositions imported without structured parts (expected for many orgs)

## Suggested Batch Cadence

For large libraries:

1. Import 500-2000 composition rows
2. Queue unknown names
3. Approve top 20-50 aliases
4. Apply canonical updates
5. Repeat

This keeps quality improving while avoiding long migration downtime.

## Handling Notes-Based Part Gaps

If source sheets track missing parts in notes (for example: "missing 2nd Trumpet"):

1. Import notes verbatim into composition comments/notes.
2. Optionally parse common patterns into a separate remediation list.
3. Treat this as follow-up work, not a blocker for composition ingest.

## Rollback Strategy

- Keep pre-import backups.
- Execute normalization updates inside transactions where feasible.
- Record import batch IDs and row counts for auditability.

## Future Upgrade Path (Optional)

When codebase support is ready, move to full FK-based composer/arranger entities (Phase B in COMPOSER_NORMALIZATION.sql).
Do not apply Phase B until UI/API/reporting paths are updated for FK joins.
