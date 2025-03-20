<?php
/**
 * Payment Configuration
 * 
 * This file controls whether the payment system is enabled or disabled
 * When PAYMENT_ENABLED is set to false, all registrations will skip payment
 * and redirect directly to the success page
 */

// Set to false to disable online payments and enable offline payments
define('PAYMENT_ENABLED', false);

// Additional payment configuration can be added here as needed
?>
