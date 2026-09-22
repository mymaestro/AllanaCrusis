-- Allow recordings such as emcee remarks and piece introductions.
-- Verify before
SHOW COLUMNS FROM recordings LIKE 'catalog_number';

ALTER TABLE recordings
    MODIFY catalog_number varchar(5) DEFAULT NULL;

-- Verify after
SHOW COLUMNS FROM recordings LIKE 'catalog_number';