<?php
include '../../includes/db_config.php';

// Get parameters from POST request
$registration_id = isset($_POST['registration_id']) ? $_POST['registration_id'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'initiated'; // Default to 'initiated'
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null; // Optional payment ID for completed/failed attempts
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null; // Amount associated with the payment attempt
$error_message = isset($_POST['error_message']) ? $_POST['error_message'] : null; // Error message if payment failed
$is_alumni = isset($_POST['alumni']) && $_POST['alumni'] == '1'; // Check if this is an alumni payment

// New fields from the table structure
$transaction_reference = isset($_POST['transaction_reference']) ? $_POST['transaction_reference'] : null;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
$payment_processor = isset($_POST['payment_processor']) ? $_POST['payment_processor'] : null;
$response_data = isset($_POST['response_data']) ? $_POST['response_data'] : null;

// Log all incoming data for debugging
error_log("Payment attempt request received - ID: $registration_id, Status: $status, Payment ID: " . ($payment_id ?? 'NULL') . ", Amount: " . ($amount ?? 'NULL') . ", Is Alumni: " . ($is_alumni ? "Yes" : "No"));

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
        if ($is_alumni) {
            // Check alumni registrations
            $fetch_stmt = $conn->prepare("SELECT 1000 as amount FROM alumni_registrations WHERE jis_id = ? LIMIT 1");
            $fetch_stmt->bind_param("s", $registration_id);
            $fetch_stmt->execute();
            $fetch_result = $fetch_stmt->get_result();
            
            if ($fetch_result->num_rows > 0) {
                $row = $fetch_result->fetch_assoc();
                $amount = floatval($row['amount']);
                error_log("Alumni amount set to: $amount");
            }
            
            $fetch_stmt->close();
        } else {
            // Check inhouse registrations
            $fetch_stmt = $conn->prepare("SELECT 400 as amount FROM registrations WHERE jis_id = ? LIMIT 1");
            $fetch_stmt->bind_param("s", $registration_id);
            $fetch_stmt->execute();
            $fetch_result = $fetch_stmt->get_result();
            
            if ($fetch_result->num_rows > 0) {
                $row = $fetch_result->fetch_assoc();
                $amount = floatval($row['amount']);
                error_log("Student amount set to: $amount");
            }
            
            $fetch_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error fetching amount from registration: " . $e->getMessage());
    }
}

// Make sure is_alumni is always set properly even if not passed in the request
$alumni_flag = $is_alumni ? 1 : 0;
// Set registration_type based on alumni flag
$registration_type = $is_alumni ? 'alumni' : 'inhouse';

// Record the payment attempt in the database with all available information
try {
    // Create base SQL query with all fields
    $sql_base = "INSERT INTO payment_attempts (
        registration_id, 
        registration_type, 
        status, 
        payment_id, 
        amount, 
        error_message, 
        attempt_time, 
        ip_address, 
        transaction_reference,
        payment_method,
        payment_processor,
        response_data,
        last_updated
    ) VALUES (
        ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, NOW()
    )";
    
    // Prepare statement with all fields
    $stmt = $conn->prepare($sql_base);
    
    // Bind all parameters (using null for optional parameters that weren't provided)
    $stmt->bind_param(
        "ssssdsssss", 
        $registration_id, 
        $registration_type,
        $status, 
        $payment_id, 
        $amount, 
        $error_message, 
        $ip, 
        $transaction_reference,
        $payment_method,
        $payment_processor
    );
    
    // Set null parameters to NULL for proper SQL insertion
    if ($payment_id === null) $stmt->bind_param(3, $null);
    if ($amount === null) $stmt->bind_param(4, $null);
    if ($error_message === null) $stmt->bind_param(5, $null);
    if ($transaction_reference === null) $stmt->bind_param(8, $null);
    if ($payment_method === null) $stmt->bind_param(9, $null);
    if ($payment_processor === null) $stmt->bind_param(10, $null);
    
    // Handle JSON response data separately due to longtext/JSON type
    if ($response_data !== null) {
        $stmt->bind_param("b", $response_data);
    } else {
        $stmt->bind_param("b", $null);
    }
    
    if ($stmt->execute()) {
        // Log success for debugging
        error_log("Payment attempt recorded successfully: Registration ID: $registration_id, Status: $status, Payment ID: " . ($payment_id ?? 'NULL') . ", Amount: " . ($amount ?? 'NULL') . ", Is Alumni: " . ($is_alumni ? "Yes" : "No"));
        
        // If payment is completed, update the registration record with payment info
        if ($status === 'completed' && $payment_id) {
            try {
                if ($is_alumni) {
                    // Update alumni registration
                    $update_stmt = $conn->prepare("UPDATE alumni_registrations SET payment_status = 'Paid', payment_id = ?, amount_paid = ?, payment_date = NOW() WHERE jis_id = ?");
                    $update_stmt->bind_param("sds", $payment_id, $amount, $registration_id);
                    $update_stmt->execute();
                    
                    if ($update_stmt->affected_rows > 0) {
                        error_log("Updated alumni registration payment: $registration_id with amount: $amount");
                    } else {
                        error_log("Warning: No alumni registration found to update for ID: $registration_id");
                    }
                } else {
                    // Update inhouse registration
                    $update_stmt = $conn->prepare("UPDATE registrations SET payment_status = 'Paid', payment_id = ?, amount_paid = ?, payment_date = NOW() WHERE jis_id = ?");
                    $update_stmt->bind_param("sds", $payment_id, $amount, $registration_id);
                    $update_stmt->execute();
                    
                    if ($update_stmt->affected_rows > 0) {
                        error_log("Updated registration payment: $registration_id with amount: $amount");
                    } else {
                        error_log("Warning: No registration found to update for ID: $registration_id");
                    }
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
                'registration_type' => $registration_type,
                'status' => $status,
                'payment_id' => $payment_id,
                'amount' => $amount,
                'ip_address' => $ip,
                'transaction_reference' => $transaction_reference,
                'payment_method' => $payment_method,
                'payment_processor' => $payment_processor,
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
