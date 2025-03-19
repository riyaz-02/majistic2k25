-- This script ensures the payment_attempts table has all required fields and indexes

-- Check if the payment_attempts table exists
CREATE TABLE IF NOT EXISTS payment_attempts (
  id INT(11) NOT NULL AUTO_INCREMENT,
  registration_id VARCHAR(50) NOT NULL,
  registration_type ENUM('inhouse', 'alumni') NOT NULL DEFAULT 'inhouse',
  status VARCHAR(20) NOT NULL DEFAULT 'initiated',
  payment_id VARCHAR(100) DEFAULT NULL,
  amount DECIMAL(10,2) DEFAULT NULL,
  error_message TEXT DEFAULT NULL,
  attempt_time DATETIME NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  transaction_reference VARCHAR(255) DEFAULT NULL,
  payment_method VARCHAR(50) DEFAULT NULL,
  payment_processor VARCHAR(50) DEFAULT NULL,
  response_data LONGTEXT DEFAULT NULL,
  last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for faster queries
CREATE INDEX IF NOT EXISTS idx_registration_id ON payment_attempts (registration_id);
CREATE INDEX IF NOT EXISTS idx_registration_type ON payment_attempts (registration_type);
CREATE INDEX IF NOT EXISTS idx_status ON payment_attempts (status);
CREATE INDEX IF NOT EXISTS idx_payment_id ON payment_attempts (payment_id);
CREATE INDEX IF NOT EXISTS idx_attempt_time ON payment_attempts (attempt_time);

-- Update structure to add any missing columns (this is safe to run multiple times)
-- These commands will only execute if the columns don't already exist

-- Check and add transaction_reference if needed
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'payment_attempts'
  AND COLUMN_NAME = 'transaction_reference'
  AND TABLE_SCHEMA = DATABASE()
);

SET @query = IF(@exist = 0, 
  'ALTER TABLE payment_attempts ADD COLUMN transaction_reference VARCHAR(255) DEFAULT NULL',
  'SELECT "Column transaction_reference already exists"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add payment_method if needed
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'payment_attempts'
  AND COLUMN_NAME = 'payment_method'
  AND TABLE_SCHEMA = DATABASE()
);

SET @query = IF(@exist = 0, 
  'ALTER TABLE payment_attempts ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL',
  'SELECT "Column payment_method already exists"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add payment_processor if needed
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'payment_attempts'
  AND COLUMN_NAME = 'payment_processor'
  AND TABLE_SCHEMA = DATABASE()
);

SET @query = IF(@exist = 0, 
  'ALTER TABLE payment_attempts ADD COLUMN payment_processor VARCHAR(50) DEFAULT NULL',
  'SELECT "Column payment_processor already exists"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add response_data if needed
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'payment_attempts'
  AND COLUMN_NAME = 'response_data'
  AND TABLE_SCHEMA = DATABASE()
);

SET @query = IF(@exist = 0, 
  'ALTER TABLE payment_attempts ADD COLUMN response_data LONGTEXT DEFAULT NULL',
  'SELECT "Column response_data already exists"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add registration_type if needed
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'payment_attempts'
  AND COLUMN_NAME = 'registration_type'
  AND TABLE_SCHEMA = DATABASE()
);

SET @query = IF(@exist = 0, 
  'ALTER TABLE payment_attempts ADD COLUMN registration_type ENUM("inhouse", "alumni") NOT NULL DEFAULT "inhouse" AFTER registration_id',
  'SELECT "Column registration_type already exists"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
