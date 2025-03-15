<?php
include '../../includes/db_config.php';

// Get parameters from POST request
$registration_id = isset($_POST['registration_id']) ? $_POST['registration_id'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'initiated'; // Default to 'initiated'
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null; // Optional payment ID for completed/failed attempts

if (empty($registration_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Registration ID is required']);
    exit;
}

// Valid statuses
$valid_statuses = ['initiated', 'completed', 'failed', 'abandoned'];
if (!in_array($status, $valid_statuses)) {
    $status = 'initiated'; // Default to initiated if invalid status
}

// Record the payment attempt in the database
try {
    if ($payment_id) {
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, attempt_time, ip_address) VALUES (?, ?, ?, NOW(), ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("ssss", $registration_id, $status, $payment_id, $ip);
    } else {
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, attempt_time, ip_address) VALUES (?, ?, NOW(), ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("sss", $registration_id, $status, $ip);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        error_log("Failed to record payment attempt: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to record payment attempt']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    error_log("Payment attempt recording error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}

$conn->close();
?>
