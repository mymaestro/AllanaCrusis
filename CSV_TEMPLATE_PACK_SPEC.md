# CSV Template Pack Specification

This specification defines the CSV templates used for first-time data onboarding into AllanaCrusis.

## Design Goals

- Support composition-first migration from spreadsheets/Google Sheets
- Minimize blockers when part inventories are incomplete
- Standardize import validation and error reporting
- Keep templates simple for non-technical librarians

## Encoding & File Rules

- File format: CSV (comma-separated)
- Encoding: UTF-8
- Header row: required, exact column names
- Empty values: allowed for optional columns
- Dates: `YYYY-MM-DD`
- Decimal separator: `.`
- Boolean fields: `0|1` (or `true|false`, normalized during import)

---

## Template Pack Contents

1. `compositions.csv` (required)
2. `composition_notes.csv` (recommended)
3. `parts.csv` (optional advanced)
4. `part_gaps.csv` (optional; for notes-based missing-part tracking)
5. `supporting_data_quickstart.csv` (optional override/customization)

---

## 1) compositions.csv (Required)

Primary import file. One row per composition/piece.

### Required Columns

- `catalog_number`
- `name`
- `composer`
- `enabled`
- `provenance`

### Recommended Columns

- `arranger`
- `genre`
- `ensemble`
- `grade`
- `duration`
- `description`
- `comments`
- `performance_notes`
- `publisher`
- `editor`
- `paper_size`
- `storage_location`
- `date_acquired`
- `cost`
- `last_inventory_date`
- `last_performance_date`
- `listening_example_link`
- `windrep_link`

### Field-Level Validation

- `catalog_number`
  - required, unique in file and database
  - max length should fit DB (`varchar(5)` in current schema; verify local policy)
- `name`
  - required, non-empty
- `composer`
  - required for onboarding quality (allow `Unknown`/`Traditional`)
- `enabled`
  - accepted: `1|0|true|false|yes|no`
- `provenance`
  - accepted canonical values: `P|R|B|D`
  - mapping: Purchased→`P`, Rented→`R`, Borrowed→`B`, Donated→`D`
- `genre`
  - must exist in supporting data (`genres.id_genre` or mapped display name)
- `ensemble`
  - must exist in supporting data (`ensembles.id_ensemble` or mapped display name)
- `grade`
  - numeric, expected range `0.0`–`7.0`
- `duration`
  - integer seconds, `>= 0`
- `paper_size`
  - must match known paper size id or mapped name
- `cost`
  - numeric decimal `>= 0`
- date fields
  - strict `YYYY-MM-DD`

### Sample Header

```csv
catalog_number,name,composer,arranger,enabled,provenance,genre,ensemble,grade,duration,description,comments,publisher,paper_size,storage_location,date_acquired,cost,last_inventory_date,last_performance_date,listening_example_link,windrep_link
```

### Sample Rows

```csv
M001,Stars and Stripes Forever,The,"Sousa, John Philip",,1,P,MARCH,WB,3.5,210,"Public domain edition",,"Public Domain",F,"Cabinet A-3",2021-09-01,0.00,2025-11-10,2024-07-04,https://example.org/listen/m001,https://www.windrep.org/Stars_and_Stripes_Forever,_The_(1896)
C101,Lincolnshire Posy,"Grainger, Percy",,1,P,CLASS,WB,5.0,900,"Suite for military band",,"Schott",F,"Cabinet B-1",2018-04-22,95.00,2025-10-03,2023-03-18,,
```

---

## 2) composition_notes.csv (Recommended)

For organizations whose rich metadata lives in free-form notes/doc columns.

### Required Columns

- `catalog_number`
- `notes_type`
- `notes_text`

### Optional Columns

- `source`
- `confidence`

### notes_type Allowed Values

- `library_note`
- `performance_note`
- `acquisition_note`
- `condition_note`
- `parts_note`

### Sample Header

```csv
catalog_number,notes_type,notes_text,source,confidence
```

### Sample Rows

```csv
M001,parts_note,"Missing 2nd Trumpet and Euphonium BC",legacy_sheet,0.95
C101,library_note,"Borrowed score must be returned by end of season",legacy_sheet,0.90
```

---

## 3) parts.csv (Optional Advanced)

For groups that already track structured part inventory.

### Required Columns

- `catalog_number`
- `id_part_type`
- `originals_count`
- `copies_count`

### Optional Columns

- `name`
- `description`
- `paper_size`
- `page_count`
- `image_path`
- `is_part_collection`

### Field-Level Validation

- `catalog_number` must exist in imported compositions
- `id_part_type` must exist in supporting data
- `originals_count`, `copies_count` integers `>= 0`
- `paper_size` valid id/name mapping
- `page_count` integer `>= 0`

### Sample Header

```csv
catalog_number,id_part_type,name,description,paper_size,page_count,originals_count,copies_count,image_path,is_part_collection
```

### Sample Rows

```csv
M001,101,Trumpet 1,,F,2,12,0,m001_trumpet1.pdf,0
M001,102,Trumpet 2,,F,2,10,2,m001_trumpet2.pdf,0
```

---

## 4) part_gaps.csv (Optional)

Bridge format for organizations that do not have full part tables but do track missing parts in notes.

### Required Columns

- `catalog_number`
- `gap_text`

### Optional Columns

- `severity`
- `source`
- `resolved`

### severity Allowed Values

- `low`
- `medium`
- `high`

### resolved Allowed Values

- `0|1|true|false`

### Sample Header

```csv
catalog_number,gap_text,severity,source,resolved
```

### Sample Rows

```csv
M001,"missing 2nd Trumpet",high,legacy_notes,0
C101,"need 1 additional Horn in F copy",medium,librarian_spreadsheet,0
```

---

## 5) supporting_data_quickstart.csv (Optional Override)

Optional customization layer on top of the standard quick-start preload.

### Required Columns

- `entity_type`
- `id`
- `name`
- `enabled`

### Optional Columns

- `description`
- `family`
- `collation`

### entity_type Allowed Values

- `genre`
- `ensemble`
- `instrument`
- `part_type`
- `paper_size`

### Sample Header

```csv
entity_type,id,name,enabled,description,family,collation
```

### Sample Rows

```csv
genre,MARCH,March,1,"Military march",,
ensemble,WB,Wind Band,1,"Full wind band",,
instrument,TRUMPET,Trumpet,1,,brass,120
paper_size,F,Folio,1,"9x12",,
```

---

## Import Validation Output Contract

The importer should produce row-level validation output with:

- `file_name`
- `row_number`
- `severity` (`error|warning|info`)
- `field_name`
- `code`
- `message`
- `suggested_fix`

### Common Validation Codes

- `REQUIRED_MISSING`
- `DUPLICATE_IN_FILE`
- `DUPLICATE_IN_DB`
- `INVALID_ENUM`
- `INVALID_DATE`
- `INVALID_NUMBER`
- `REFERENCE_NOT_FOUND`
- `TEXT_TOO_LONG`
- `FORMAT_WARNING`

---

## Mapping Rules (Spreadsheet/Google Sheets)

- Users can map custom source headers to canonical template headers.
- Save mapping profiles by organization and template type.
- If mapping confidence is low, mark as warning and require confirmation.

---

## Recommended First-Time Import Order

1. Quick-start supporting data preload
2. `compositions.csv`
3. `composition_notes.csv`
4. Name normalization review/apply cycle
5. `part_gaps.csv` (if used)
6. `parts.csv` (if available)

---

## Minimum Viable Onboarding Definition

A migration is considered successful when:

- Compositions are searchable by title/composer/catalog
- Core filters (genre, ensemble, grade) work for most rows
- Composer/arranger normalization queue is generated
- Notes-based part gaps are retained for future remediation
