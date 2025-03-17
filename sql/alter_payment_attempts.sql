-- SQL query to alter payment_attempts table with new fields

ALTER TABLE `payment_attempts`
ADD COLUMN `payment_method` varchar(50) DEFAULT NULL COMMENT 'Payment method used (UPI, Credit Card, etc.)' AFTER `amount`,
ADD COLUMN `transaction_reference` varchar(100) DEFAULT NULL COMMENT 'External transaction reference' AFTER `payment_id`,
ADD COLUMN `payment_processor` varchar(50) DEFAULT NULL COMMENT 'Payment gateway/processor used' AFTER `payment_method`,
ADD COLUMN `response_data` JSON DEFAULT NULL COMMENT 'JSON response from payment gateway' AFTER `error_message`,
ADD COLUMN `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
ADD COLUMN `registration_type` varchar(20) DEFAULT 'inhouse' COMMENT 'Type of registration (inhouse, outhouse, alumni)' AFTER `user_id`;

-- Add index for better query performance
ALTER TABLE `payment_attempts` 
ADD INDEX `idx_payment_method` (`payment_method`),
ADD INDEX `idx_transaction_reference` (`transaction_reference`),
ADD INDEX `idx_registration_type` (`registration_type`);
