-- SQL query to create alumni_registrations table

CREATE TABLE IF NOT EXISTS `alumni_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumni_name` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `jis_id` varchar(20) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `passout_year` varchar(4) NOT NULL,
  `current_organization` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('Paid', 'Not Paid') NOT NULL DEFAULT 'Not Paid',
  `payment_id` varchar(50) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jis_id` (`jis_id`),
  KEY `mobile` (`mobile`),
  KEY `email` (`email`),
  KEY `passout_year` (`passout_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
