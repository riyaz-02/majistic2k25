-- Create registrations table for in-house students
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `jis_id` varchar(20) NOT NULL UNIQUE,
  `mobile` varchar(15) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `roll_no` varchar(30) NOT NULL UNIQUE,
  `department` varchar(50) NOT NULL,
  `inhouse_competition` enum('Yes', 'No') DEFAULT 'No',
  `competition_name` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('Paid','Not Paid') NOT NULL DEFAULT 'Not Paid',
  `payment_id` varchar(50) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jis_id` (`jis_id`),
  KEY `idx_email` (`email`),
  KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create registrations_outhouse table for external students
CREATE TABLE IF NOT EXISTS `registrations_outhouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `college_name` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `department` varchar(50) NOT NULL,
  `outreach_competition` enum('Yes', 'No') DEFAULT 'No',
  `competition_name` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('Paid','Not Paid') NOT NULL DEFAULT 'Not Paid',
  `payment_id` varchar(50) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payment_attempts table to track payment attempts
CREATE TABLE IF NOT EXISTS `payment_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` varchar(100) NOT NULL, -- Can be JIS ID or email based on registration type
  `status` enum('initiated', 'completed', 'failed', 'abandoned') NOT NULL,
  `attempt_time` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_registration_id` (`registration_id`),
  KEY `idx_status` (`status`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
