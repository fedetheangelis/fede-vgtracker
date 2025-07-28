-- Add site_metadata table to track version and last update time
CREATE TABLE IF NOT EXISTS site_metadata (
    id INT PRIMARY KEY DEFAULT 1,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    version VARCHAR(20) NOT NULL DEFAULT '1.0.0',
    CONSTRAINT single_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial row if it doesn't exist
INSERT IGNORE INTO site_metadata (id, version) VALUES (1, '1.0.0');
