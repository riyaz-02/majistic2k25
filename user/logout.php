<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../includes/db_config.php';

// Update logout time in login_sessions table if session_id is available
if (isset($_SESSION['login_session_id'])) {
    try {
        // Get current time in IST (relies on timezone set in db_config.php)
        $now = date('Y-m-d H:i:s');
        
        $updateLogout = $db->prepare("UPDATE login_sessions SET 
            logout_time = :now, 
            session_status = 'ended' 
            WHERE id = :session_id");
        $updateLogout->execute([
            ':now' => $now,
            ':session_id' => $_SESSION['login_session_id']
        ]);
        
        // Log the logout for debugging
        error_log("User logged out successfully. Updated session ID: " . $_SESSION['login_session_id'] . " at " . $now);
    } catch (PDOException $e) {
        error_log("Failed to update logout time: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with success message
header("Location: login.php?logout=success");
exit();
?>
