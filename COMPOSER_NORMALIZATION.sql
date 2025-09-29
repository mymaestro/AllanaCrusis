-- Composer/Arranger Normalization Database Schema
-- This approach creates normalized tables for composers and arrangers

-- Create composers table
CREATE TABLE `composers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_normalized` varchar(255) NOT NULL COMMENT 'Normalized name format: Last, First Middle',
  `name_display` varchar(255) NOT NULL COMMENT 'Preferred display format',
  `birth_year` int(4) DEFAULT NULL,
  `death_year` int(4) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Biographical notes',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_normalized` (`name_normalized`),
  KEY `name_display` (`name_display`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create arrangers table (similar structure)
CREATE TABLE `arrangers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_normalized` varchar(255) NOT NULL COMMENT 'Normalized name format: Last, First Middle',
  `name_display` varchar(255) NOT NULL COMMENT 'Preferred display format',
  `birth_year` int(4) DEFAULT NULL,
  `death_year` int(4) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Biographical notes',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_normalized` (`name_normalized`),
  KEY `name_display` (`name_display`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add composer/arranger aliases table for alternate spellings
CREATE TABLE `composer_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `composer_id` int(11) NOT NULL,
  `alias_name` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `composer_id` (`composer_id`),
  KEY `alias_name` (`alias_name`),
  CONSTRAINT `fk_composer_aliases_composer` FOREIGN KEY (`composer_id`) REFERENCES `composers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `arranger_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arranger_id` int(11) NOT NULL,
  `alias_name` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `arranger_id` (`arranger_id`),
  KEY `alias_name` (`alias_name`),
  CONSTRAINT `fk_arranger_aliases_arranger` FOREIGN KEY (`arranger_id`) REFERENCES `arrangers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update compositions table to use foreign keys
ALTER TABLE `compositions` 
ADD COLUMN `composer_id` int(11) DEFAULT NULL AFTER `composer`,
ADD COLUMN `arranger_id` int(11) DEFAULT NULL AFTER `arranger`,
ADD KEY `composer_id` (`composer_id`),
ADD KEY `arranger_id` (`arranger_id`),
ADD CONSTRAINT `fk_compositions_composer` FOREIGN KEY (`composer_id`) REFERENCES `composers` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_compositions_arranger` FOREIGN KEY (`arranger_id`) REFERENCES `arrangers` (`id`) ON DELETE SET NULL;

-- Example data
INSERT INTO `composers` (`name_normalized`, `name_display`, `birth_year`, `death_year`, `nationality`) VALUES
('Sousa, John Philip', 'John Philip Sousa', 1854, 1932, 'American'),
('Mozart, Wolfgang Amadeus', 'Wolfgang Amadeus Mozart', 1756, 1791, 'Austrian'),
('Williams, John', 'John Williams', 1932, NULL, 'American'),
('Hall, Robert B.', 'Robert B. Hall', 1858, 1907, 'American');

-- Example aliases for common misspellings
INSERT INTO `composer_aliases` (`composer_id`, `alias_name`) VALUES
((SELECT id FROM composers WHERE name_normalized = 'Sousa, John Philip'), 'Sousa, J P'),
((SELECT id FROM composers WHERE name_normalized = 'Sousa, John Philip'), 'Sousa, J. P.'),
((SELECT id FROM composers WHERE name_normalized = 'Sousa, John Philip'), 'John P. Sousa'),
((SELECT id FROM composers WHERE name_normalized = 'Sousa, John Philip'), 'J. P. Sousa');