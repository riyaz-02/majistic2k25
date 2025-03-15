<?php
include '../../includes/db_config.php';

// Get parameters from POST request
$registration_id = isset($_POST['registration_id']) ? $_POST['registration_id'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'initiated'; // Default to 'initiated'
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null; // Optional payment ID for completed/failed attempts
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null; // Amount associated with the payment attempt
$error_message = isset($_POST['error_message']) ? $_POST['error_message'] : null; // Error message if payment failed

// Log all incoming data for debugging
error_log("Payment attempt request received - ID: $registration_id, Status: $status, Payment ID: " . ($payment_id ?? 'NULL') . ", Amount: " . ($amount ?? 'NULL'));

// Validate required parameters
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

// Get real client IP address
function getClientIP() {
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    
    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain multiple IPs separated by commas
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipList as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        }
    }
    
    // If above methods fail, use REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'];
}

$ip = getClientIP();

// If amount is not provided but should be (especially for completed status), 
// try to fetch it from the registration record
if ($status === 'completed' && $amount === null && $payment_id !== null) {
    try {
        // First check inhouse registrations
        $fetch_stmt = $conn->prepare("SELECT amount FROM registrations WHERE jis_id = ? LIMIT 1");
        $fetch_stmt->bind_param("s", $registration_id);
        $fetch_stmt->execute();
        $fetch_result = $fetch_stmt->get_result();
        
        if ($fetch_result->num_rows > 0) {
            $row = $fetch_result->fetch_assoc();
            $amount = floatval($row['amount']);
            error_log("Amount fetched from inhouse registration: $amount");
        } else {
            // If not found, check outhouse registrations
            $fetch_stmt = $conn->prepare("SELECT amount FROM registrations_outhouse WHERE college_id = ? LIMIT 1");
            $fetch_stmt->bind_param("s", $registration_id);
            $fetch_stmt->execute();
            $fetch_result = $fetch_stmt->get_result();
            
            if ($fetch_result->num_rows > 0) {
                $row = $fetch_result->fetch_assoc();
                $amount = floatval($row['amount']);
                error_log("Amount fetched from outhouse registration: $amount");
            }
        }
        
        $fetch_stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching amount from registration: " . $e->getMessage());
    }
}

// Record the payment attempt in the database with all available information
try {
    // Prepare the appropriate query based on available data
    if ($payment_id && $amount !== null && $error_message) {
        // Full data available
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, amount, error_message, attempt_time, ip_address) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssdss", $registration_id, $status, $payment_id, $amount, $error_message, $ip);
    } 
    else if ($payment_id && $amount !== null) {
        // Payment ID and amount available, no error
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, amount, attempt_time, ip_address) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssds", $registration_id, $status, $payment_id, $amount, $ip);
    }
    else if ($payment_id && $error_message) {
        // Payment ID and error available, no amount
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, error_message, attempt_time, ip_address) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssss", $registration_id, $status, $payment_id, $error_message, $ip);
    }
    else if ($amount !== null && $error_message) {
        // Amount and error available, no payment ID
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, amount, error_message, attempt_time, ip_address) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssdss", $registration_id, $status, $amount, $error_message, $ip);
    }
    else if ($payment_id) {
        // Only payment ID available
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, attempt_time, ip_address) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssss", $registration_id, $status, $payment_id, $ip);
    }
    else if ($amount !== null) {
        // Only amount available
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, amount, attempt_time, ip_address) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssds", $registration_id, $status, $amount, $ip);
    }
    else if ($error_message) {
        // Only error message available
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, error_message, attempt_time, ip_address) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssss", $registration_id, $status, $error_message, $ip);
    }
    else {
        // Basic data only
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, attempt_time, ip_address) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("sss", $registration_id, $status, $ip);
    }
    
    if ($stmt->execute()) {
        // Log success for debugging
        error_log("Payment attempt recorded successfully: Registration ID: $registration_id, Status: $status, Payment ID: " . ($payment_id ?? 'NULL') . ", Amount: " . ($amount ?? 'NULL'));
        
        // If payment is completed, update the registration record with payment info
        if ($status === 'completed' && $payment_id) {
            try {
                // First try updating inhouse registration
                $update_stmt = $conn->prepare("UPDATE registrations SET payment_status = 'Paid', payment_id = ?, amount_paid = ?, payment_date = NOW() WHERE jis_id = ?");
                $update_stmt->bind_param("sds", $payment_id, $amount, $registration_id);
                $update_stmt->execute();
                
                if ($update_stmt->affected_rows === 0) {
                    // If no rows affected, try updating outhouse registration
                    $update_stmt = $conn->prepare("UPDATE registrations_outhouse SET payment_status = 'Paid', payment_id = ?, amount_paid = ?, payment_date = NOW() WHERE college_id = ?");
                    $update_stmt->bind_param("sds", $payment_id, $amount, $registration_id);
                    $update_stmt->execute();
                    
                    if ($update_stmt->affected_rows > 0) {
                        error_log("Updated outhouse registration payment: $registration_id with amount: $amount");
                    } else {
                        error_log("Warning: No registration found to update for ID: $registration_id");
                    }
                } else {
                    error_log("Updated inhouse registration payment: $registration_id with amount: $amount");
                }
                
                $update_stmt->close();
            } catch (Exception $e) {
                error_log("Failed to update registration payment status: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment attempt recorded successfully',
            'data' => [
                'registration_id' => $registration_id,
                'status' => $status,
                'payment_id' => $payment_id,
                'amount' => $amount,
                'ip_address' => $ip,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        http_response_code(500);
        error_log("Failed to record payment attempt: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to record payment attempt: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    error_log("Payment attempt recording error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
