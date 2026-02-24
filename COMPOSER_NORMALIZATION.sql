-- COMPOSER_NORMALIZATION.sql
--
-- Purpose:
--   Import-assistive normalization for composer/arranger values with a SAFE first phase.
--
-- Why this shape:
--   The application currently reads/writes textual `compositions.composer` and `compositions.arranger`
--   in many places. Immediate FK migration would be disruptive for onboarding.
--
-- Strategy:
--   Phase A (non-breaking): alias/canonical mapping + review queue + cleanup updates
--   Phase B (optional future): full normalized entity tables + foreign-key migration

-- ============================================================
-- PHASE A: NON-BREAKING, IMPORT-ASSISTIVE NORMALIZATION
-- ============================================================

CREATE TABLE IF NOT EXISTS `name_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` enum('composer','arranger') NOT NULL,
  `alias_name` varchar(255) NOT NULL,
  `canonical_name` varchar(255) NOT NULL COMMENT 'Preferred canonical format, typically Last, First Middle',
  `confidence` tinyint(3) unsigned NOT NULL DEFAULT 100 COMMENT '100=human-confirmed, lower=heuristic',
  `source` varchar(64) DEFAULT 'manual' COMMENT 'manual|import|heuristic|seed',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_entity_alias` (`entity_type`, `alias_name`),
  KEY `idx_entity_canonical` (`entity_type`, `canonical_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `name_normalization_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` enum('composer','arranger') NOT NULL,
  `raw_name` varchar(255) NOT NULL,
  `suggested_canonical_name` varchar(255) DEFAULT NULL,
  `sample_catalog_number` varchar(20) DEFAULT NULL,
  `sample_composition_name` varchar(255) DEFAULT NULL,
  `occurrence_count` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','rejected','resolved') NOT NULL DEFAULT 'pending',
  `review_notes` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_entity_raw` (`entity_type`, `raw_name`),
  KEY `idx_review_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed examples (idempotent)
INSERT INTO `name_aliases` (`entity_type`, `alias_name`, `canonical_name`, `confidence`, `source`)
VALUES
('composer', 'Sousa, J P', 'Sousa, John Philip', 100, 'seed'),
('composer', 'Sousa, J. P.', 'Sousa, John Philip', 100, 'seed'),
('composer', 'John P. Sousa', 'Sousa, John Philip', 100, 'seed'),
('composer', 'J. P. Sousa', 'Sousa, John Philip', 100, 'seed')
ON DUPLICATE KEY UPDATE
  `canonical_name` = VALUES(`canonical_name`),
  `confidence` = GREATEST(`confidence`, VALUES(`confidence`)),
  `source` = VALUES(`source`);

-- ------------------------------------------------------------
-- Operational query A: queue unknown composer names for review
-- ------------------------------------------------------------
INSERT INTO `name_normalization_review`
  (`entity_type`, `raw_name`, `sample_catalog_number`, `sample_composition_name`, `occurrence_count`)
SELECT
  'composer' AS entity_type,
  c.composer AS raw_name,
  MIN(c.catalog_number) AS sample_catalog_number,
  MIN(c.name) AS sample_composition_name,
  COUNT(*) AS occurrence_count
FROM compositions c
LEFT JOIN name_aliases a
  ON a.entity_type = 'composer'
 AND LOWER(TRIM(a.alias_name)) = LOWER(TRIM(c.composer))
WHERE c.composer IS NOT NULL
  AND TRIM(c.composer) <> ''
  AND a.id IS NULL
GROUP BY c.composer
ON DUPLICATE KEY UPDATE
  `occurrence_count` = VALUES(`occurrence_count`),
  `sample_catalog_number` = VALUES(`sample_catalog_number`),
  `sample_composition_name` = VALUES(`sample_composition_name`);

-- ------------------------------------------------------------
-- Operational query B: queue unknown arranger names for review
-- ------------------------------------------------------------
INSERT INTO `name_normalization_review`
  (`entity_type`, `raw_name`, `sample_catalog_number`, `sample_composition_name`, `occurrence_count`)
SELECT
  'arranger' AS entity_type,
  c.arranger AS raw_name,
  MIN(c.catalog_number) AS sample_catalog_number,
  MIN(c.name) AS sample_composition_name,
  COUNT(*) AS occurrence_count
FROM compositions c
LEFT JOIN name_aliases a
  ON a.entity_type = 'arranger'
 AND LOWER(TRIM(a.alias_name)) = LOWER(TRIM(c.arranger))
WHERE c.arranger IS NOT NULL
  AND TRIM(c.arranger) <> ''
  AND a.id IS NULL
GROUP BY c.arranger
ON DUPLICATE KEY UPDATE
  `occurrence_count` = VALUES(`occurrence_count`),
  `sample_catalog_number` = VALUES(`sample_catalog_number`),
  `sample_composition_name` = VALUES(`sample_composition_name`);

-- ----------------------------------------------------------------
-- Operational query C: apply canonical updates using approved aliases
-- ----------------------------------------------------------------
UPDATE compositions c
JOIN name_aliases a
  ON a.entity_type = 'composer'
 AND LOWER(TRIM(a.alias_name)) = LOWER(TRIM(c.composer))
SET c.composer = a.canonical_name
WHERE c.composer IS NOT NULL
  AND TRIM(c.composer) <> ''
  AND c.composer <> a.canonical_name;

UPDATE compositions c
JOIN name_aliases a
  ON a.entity_type = 'arranger'
 AND LOWER(TRIM(a.alias_name)) = LOWER(TRIM(c.arranger))
SET c.arranger = a.canonical_name
WHERE c.arranger IS NOT NULL
  AND TRIM(c.arranger) <> ''
  AND c.arranger <> a.canonical_name;

-- ------------------------------------------------------------
-- Optional reporting view for UI/MCP quality checks
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW `v_name_normalization_health` AS
SELECT
  'composer' AS entity_type,
  COUNT(*) AS total_rows,
  SUM(CASE WHEN c.composer IS NULL OR TRIM(c.composer) = '' THEN 1 ELSE 0 END) AS blank_rows,
  SUM(CASE WHEN r.id IS NOT NULL AND r.status = 'pending' THEN 1 ELSE 0 END) AS pending_review_rows
FROM compositions c
LEFT JOIN name_normalization_review r
  ON r.entity_type = 'composer'
 AND r.raw_name = c.composer
UNION ALL
SELECT
  'arranger' AS entity_type,
  COUNT(*) AS total_rows,
  SUM(CASE WHEN c.arranger IS NULL OR TRIM(c.arranger) = '' THEN 1 ELSE 0 END) AS blank_rows,
  SUM(CASE WHEN r.id IS NOT NULL AND r.status = 'pending' THEN 1 ELSE 0 END) AS pending_review_rows
FROM compositions c
LEFT JOIN name_normalization_review r
  ON r.entity_type = 'arranger'
 AND r.raw_name = c.arranger;


-- ============================================================
-- PHASE B: OPTIONAL FUTURE FULL NORMALIZATION (BREAKING CHANGE)
-- ============================================================
-- Notes:
--   Keep this for a later modernization phase, after code has moved from
--   text fields to FK-backed joins across UI/API/search/reporting.
--
-- Potential future tables:
--   composers(id, name_normalized, name_display, ...)
--   arrangers(id, name_normalized, name_display, ...)
--   compositions.composer_id / compositions.arranger_id FKs
--
-- Do not apply Phase B until the application has explicit compatibility support.