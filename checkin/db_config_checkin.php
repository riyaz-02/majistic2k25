<?php
/**
 * Database configuration file specifically for the Check-in system
 * This allows the check-in system to use a direct connection independent of other parts
 */

// Error reporting for debugging - can be commented out in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Database connection credentials for Hostinger
// Using the provided credentials
$db_host = 'srv1834.hstgr.io';  // Hostinger server hostname
$db_user = 'u901957751_majistic';  // Database username
$db_pass = '#4Szt|/DYj';  // Database password
$db_name = 'u901957751_majistic2025';  // Database name

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    // Log error to file rather than displaying it
    error_log("Check-in system connection failed: " . $conn->connect_error);
    
    // Try alternate connection with different host if first attempt fails
    $conn = new mysqli('localhost', $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        // Try third alternative with domain name as host
        $conn = new mysqli('majistic.org', $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            error_log("Check-in system all connection attempts failed: " . $conn->connect_error);
            // Connection still failed - will be handled by the including script
        } else {
            // Set character set
            $conn->set_charset("utf8mb4");
        }
    } else {
        // Set character set
        $conn->set_charset("utf8mb4");
    }
} else {
    // Set character set
    $conn->set_charset("utf8mb4");
}
?>
