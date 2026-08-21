-- Run once on an existing installation to enable composition loan packages.
ALTER TABLE download_tokens
    MODIFY id_playgram INT(11) DEFAULT NULL,
    MODIFY id_section INT(10) UNSIGNED DEFAULT NULL,
    ADD COLUMN catalog_number VARCHAR(5) DEFAULT NULL AFTER id_section,
    ADD INDEX (catalog_number),
    ADD CONSTRAINT fk_download_tokens_composition
        FOREIGN KEY (catalog_number) REFERENCES compositions(catalog_number)
        ON DELETE CASCADE;