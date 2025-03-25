<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    header('Location: ../login.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (empty($type) || $id <= 0) {
        $_SESSION['error_message'] = "Invalid registration specified.";
        header('Location: index.php');
        exit;
    }
    
    // Determine the table based on registration type
    $table = $type === 'student' ? 'registrations' : 'alumni_registrations';
    
    try {
        if (isset($_POST['mark_as_paid'])) {
            // Update payment status to Paid
            $query = "UPDATE $table SET payment_status = 'Paid' WHERE id = :id";
            $status_message = "Payment status updated to Paid!";
        } elseif (isset($_POST['mark_as_unpaid'])) {
            // Update payment status to Not Paid
            $query = "UPDATE $table SET payment_status = 'Not Paid' WHERE id = :id";
            $status_message = "Payment status updated to Not Paid!";
        } else {
            $_SESSION['error_message'] = "Invalid action specified.";
            header("Location: view_registration.php?type=$type&id=$id");
            exit;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = $status_message;
        } else {
            $_SESSION['error_message'] = "Failed to update payment status.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    
    // Redirect back to view page
    header("Location: view_registration.php?type=$type&id=$id");
    exit;
} else {
    // If accessed directly without form submission
    header('Location: index.php');
    exit;
}
?>
