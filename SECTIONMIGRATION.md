● Looking at the current structure:

  Current: section_part_types table links sections → part_typesNew: Need to link sections → instruments instead

● Here's the migration to change sections from referencing part_types to instruments:

● -- Migration: Change sections to reference instruments instead of part_types

  -- Step 1: Create new section_instruments table
  CREATE TABLE `section_instruments` (
    `id_section` int(10) unsigned NOT NULL COMMENT 'Section ID',
    `id_instrument` int(10) unsigned NOT NULL COMMENT 'Instrument ID',
    PRIMARY KEY (`id_section`, `id_instrument`),
    KEY `fk_section_instruments_section` (`id_section`),
    KEY `fk_section_instruments_instrument` (`id_instrument`),
    CONSTRAINT `fk_section_instruments_section` FOREIGN KEY (`id_section`) REFERENCES `sections` (`id_section`) ON DELETE CASCADE,
    CONSTRAINT `fk_section_instruments_instrument` FOREIGN KEY (`id_instrument`) REFERENCES `instruments` (`id_instrument`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Links sections to instruments (many-to-many)';

  -- Step 2: Migrate existing data from section_part_types to section_instruments
  -- This maps part_types to their default instruments
  INSERT INTO `section_instruments` (`id_section`, `id_instrument`)
  SELECT DISTINCT
      spt.id_section,
      pt.default_instrument
  FROM `section_part_types` spt
  JOIN `part_types` pt ON spt.id_part_type = pt.id_part_type
  WHERE pt.default_instrument IS NOT NULL;

  -- Step 3: Handle part collections - add instruments from part_collections
  INSERT IGNORE INTO `section_instruments` (`id_section`, `id_instrument`)
  SELECT DISTINCT
      spt.id_section,
      pc.id_instrument_key
  FROM `section_part_types` spt
  JOIN `part_types` pt ON spt.id_part_type = pt.id_part_type
  JOIN `part_collections` pc ON pt.id_part_type = pc.id_part_type_key
  WHERE pt.is_part_collection IS NOT NULL;

  -- Step 4: Drop the old section_part_types table
  DROP TABLE `section_part_types`;

  -- Step 5: Update download_tokens table to work with the new structure
  -- (The download_tokens table references sections, which is still valid)

● This migration:
  1. Creates section_instruments table linking sections directly to instruments
  2. Migrates existing data by mapping part_types to their default instruments
  3. Includes instruments from part_collections
  4. Removes the old section_part_types table

  The sections table itself doesn't need to change - just the relationship table.


  ● Here are some queries to test that the sections migration worked correctly:

  1. Compare instrument counts before/after migration:
  -- Count instruments per section in new structure
  SELECT
      s.name AS section_name,
      COUNT(si.id_instrument) AS instrument_count,
      GROUP_CONCAT(i.name ORDER BY i.name) AS instruments
  FROM sections s
  LEFT JOIN section_instruments si ON s.id_section = si.id_section
  LEFT JOIN instruments i ON si.id_instrument = i.id_instrument
  WHERE s.enabled = 1
  GROUP BY s.id_section, s.name
  ORDER BY s.name;

  2. Check for any missing instruments (instruments without sections):
  -- Find instruments not assigned to any section
  SELECT
      i.id_instrument,
      i.name AS instrument_name,
      i.family,
      i.enabled
  FROM instruments i
  LEFT JOIN section_instruments si ON i.id_instrument = si.id_instrument
  WHERE si.id_instrument IS NULL
  AND i.enabled = 1;

  3. Verify section coverage by instrument family:
  -- Show how instrument families are distributed across sections
  SELECT
      s.name AS section_name,
      i.family AS instrument_family,
      COUNT(*) as count,
      GROUP_CONCAT(i.name ORDER BY i.name) as instruments
  FROM sections s
  JOIN section_instruments si ON s.id_section = si.id_section
  JOIN instruments i ON si.id_instrument = i.id_instrument
  WHERE s.enabled = 1 AND i.enabled = 1
  GROUP BY s.id_section, s.name, i.family
  ORDER BY s.name, i.family;

  4. Test that download tokens still work with sections:
  -- Verify sections are properly referenced in download_tokens
  SELECT
      dt.token,
      s.name AS section_name,
      p.name AS playgram_name,
      dt.expires_at
  FROM download_tokens dt
  JOIN sections s ON dt.id_section = s.id_section
  JOIN playgrams p ON dt.id_playgram = p.id_playgram
  LIMIT 5;

  These queries will help verify the migration preserved all the relationships correctly.