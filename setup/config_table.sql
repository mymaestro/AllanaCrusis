-- Configuration table for system settings
-- All settings can be modified by admins through the web interface

CREATE TABLE IF NOT EXISTS `config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `config_key` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Configuration key (e.g., ORGNAME)',
  `value` LONGTEXT NOT NULL COMMENT 'Configuration value',
  `type` ENUM('string', 'integer', 'boolean', 'url', 'path', 'email') NOT NULL DEFAULT 'string' COMMENT 'Data type for validation',
  `description` TEXT COMMENT 'Description of what this setting does',
  `usage` TEXT COMMENT 'Where and how this setting is used in the application',
  `default_value` LONGTEXT COMMENT 'Default value if not set',
  `is_required` BOOLEAN DEFAULT FALSE COMMENT 'Whether this setting is required',
  `is_readonly` BOOLEAN DEFAULT FALSE COMMENT 'If true, cannot be edited via web interface',
  `category` VARCHAR(100) COMMENT 'Settings category (e.g., Organization, Paths, Email, System)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` VARCHAR(255) COMMENT 'Username of last person to update this setting',
  INDEX idx_category (category),
  INDEX idx_config_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration values
INSERT INTO `config` (`config_key`, `value`, `type`, `description`, `usage`, `default_value`, `is_required`, `is_readonly`, `category`, `updated_by`) VALUES
('ORGNAME', '4th Wind', 'string', 'Organization name', 'Displayed in page titles, headers, and browser tabs throughout the application', '4th Wind', 1, 0, 'Organization', 'system'),
('ORGDESC', 'Fourth Wind Wind Ensemble', 'string', 'Organization description', 'Used in meta tags for SEO and displayed on the home page', 'Fourth Wind Wind Ensemble', 1, 0, 'Organization', 'system'),
('ORGHOME', 'https://library.allanacrusis.com/', 'url', 'Organization website home URL', 'Base URL for most links users see. Used in navigation, breadcrumbs, and redirect URLs', 'https://library.allanacrusis.com/', 1, 0, 'Organization', 'system'),
('ORGLOGO', 'images/logo.png', 'string', 'Logo image path (relative to public directory)', 'Displayed in page headers and navigation bars', 'images/logo.png', 0, 0, 'Organization', 'system'),
('ORGMAIL', 'librarian@allanacrusis.com', 'email', 'Organization contact email', 'Used in contact forms, email notifications, and the footer', 'librarian@allanacrusis.com', 0, 0, 'Organization', 'system'),
('ORGRECORDINGS', 'https://library.allanacrusis.com/files/recordings/', 'url', 'Public URL for accessing recordings', 'Used to construct download URLs for recordings accessible to users. Useful if you store recordings on a separate server or CDN', 'https://library.allanacrusis.com/files/recordings/', 1, 0, 'Paths', 'system'),
('DOWNLOAD_TOKEN_EXPIRY_DAYS', '5', 'integer', 'Number of days download tokens remain valid', 'Controls how long temporary download links work before they expire. Used when generating distribution links', '5', 0, 0, 'System', 'system'),
('REGION', 'HOME', 'string', 'Region/location identifier', 'Used to identify different library locations or branches in multi-location setups', 'HOME', 0, 0, 'System', 'system'),
('DEBUG', '1', 'boolean', 'Enable debug mode (0=off, 1=on)', 'When enabled, writes detailed debugging information to application error logs. Disable in production for better performance', '0', 0, 0, 'System', 'system');
