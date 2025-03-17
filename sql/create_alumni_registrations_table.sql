-- SQL query to create registrations_alumni table

CREATE TABLE IF NOT EXISTS `registrations_alumni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `passout_year` varchar(4) NOT NULL,
  `department` varchar(100) NOT NULL,
  `current_organization` varchar(100) DEFAULT NULL,
  `current_role` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT 'Alumni Meet',
  `participation_type` varchar(50) DEFAULT NULL COMMENT 'Speaker, Attendee, Panel Member, etc.',
  `payment_status` varchar(20) DEFAULT 'Not Paid',
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `payment_date` datetime DEFAULT NULL,
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_passout_year` (`passout_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
