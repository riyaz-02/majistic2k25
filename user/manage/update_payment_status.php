<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Set default return URL
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php?page=all_registrations';

// Process payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_paid'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (empty($type) || $id <= 0) {
        $_SESSION['error_message'] = 'Invalid registration details.';
        header("Location: $return_url");
        exit;
    }
    
    // Determine which table to update
    $table = ($type === 'student') ? 'registrations' : 'alumni_registrations';
    $name_field = ($type === 'student') ? 'student_name' : 'alumni_name';
    
    try {
        // First check if the registration exists and get the name
        $query = "SELECT $name_field FROM $table WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registration) {
            $_SESSION['error_message'] = 'Registration not found.';
            header("Location: $return_url");
            exit;
        }
        
        $name = $registration[$name_field];
        
        // Update the payment status
        $receipt_number = 'RECEIPT-' . time() . '-' . mt_rand(1000, 9999);
        $current_time = date('Y-m-d H:i:s');
        $default_amount = ($type === 'student') ? 500 : 1000;
        
        $query = "UPDATE $table SET 
                  payment_status = 'Paid', 
                  payment_date = :payment_date, 
                  receipt_number = :receipt_number,
                  paid_amount = :paid_amount
                  WHERE id = :id";
                  
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':payment_date' => $current_time,
            ':receipt_number' => $receipt_number,
            ':paid_amount' => $default_amount,
            ':id' => $id
        ]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Payment status updated successfully for $name.";
        } else {
            $_SESSION['error_message'] = "Failed to update payment status. Registration may be already marked as paid.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        error_log("Payment status update error: " . $e->getMessage());
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back
header("Location: $return_url");
exit;
?>
