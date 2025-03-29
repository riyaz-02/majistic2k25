<?php
/**
 * Simple Page Visit Counter
 * This script handles counting and storing page visits for the maJIStic website
 */

function getVisitCount() {
    $counterFile = $_SERVER['DOCUMENT_ROOT'] . '/majistic/includes/visit_count.txt';
    
    // Create the file if it doesn't exist
    if (!file_exists($counterFile)) {
        file_put_contents($counterFile, '0');
        chmod($counterFile, 0644); // Set appropriate permissions
    }
    
    // Check if this is a new session
    if (!isset($_SESSION['counted']) || $_SESSION['counted'] !== true) {
        // Get current count
        $currentCount = intval(file_get_contents($counterFile));
        
        // Increment the count
        $currentCount++;
        
        // Save the new count
        file_put_contents($counterFile, strval($currentCount));
        
        // Mark this session as counted
        $_SESSION['counted'] = true;
    }
    
    // Return the formatted number with commas
    return number_format(intval(file_get_contents($counterFile)));
}

// Make sure session is started to track visits properly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
