<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || 
    ($_SESSION['admin_role'] !== 'Manage Website' && $_SESSION['admin_role'] !== 'Super Admin')) {
    header('Location: ../login.php');
    exit;
}

// Check if admin ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    $admin_id = intval($_POST['admin_id']);
    
    // Don't allow deletion of own account
    if ($admin_id === $_SESSION['admin_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account.";
        header('Location: index.php?page=admin_users');
        exit;
    }
    
    try {
        // Delete the admin user
        $query = "DELETE FROM admin_users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $admin_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Admin user deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete admin user or user not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    // Redirect back to admin users page
    header('Location: index.php?page=admin_users');
    exit;
} else {
    // If no ID provided, redirect to admin users page
    header('Location: index.php?page=admin_users');
    exit;
}
?>
