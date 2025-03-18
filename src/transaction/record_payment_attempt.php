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
$payment_processor = isset($_POST['payment_processor']) ? $_POST['payment_processor'] : 'Razorpay'; // Default to Razorpay
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
$valid_statuses = ['initiated', 'completed', 'failed', 'abandoned', 'error'];
if (!in_array($status, $valid_statuses)) {
    $status = 'initiated'; // Default to initiated if invalid status
}

// Get real client IP address - enhanced version
function getClientIP() {
    // Check for Cloudflare
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    
    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain multiple IPs separated by commas
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipList as $ip) {
            if (filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        }
    }
    
    // Check for AWS ELB
    if (!empty($_SERVER['HTTP_X_FORWARDED_AWS_ELB'])) {
        return $_SERVER['HTTP_X_FORWARDED_AWS_ELB'];
    }
    
    // If above methods fail, use REMOTE_ADDR
    return $_SERVER['REMOTE_ADDR'];
}

$ip = getClientIP();

// If amount is not provided but should be (especially for completed status), 
// try to fetch it from the registration record
if (($status === 'completed' || $status === 'initiated') && $amount === null) {
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
            $fetch_stmt = $conn->prepare("SELECT 500 as amount FROM registrations WHERE jis_id = ? LIMIT 1");
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

// Set registration_type based on alumni flag
$registration_type = $is_alumni ? 'alumni' : 'inhouse';

// Record the payment attempt in the database with all available information
try {
    // Create base SQL query with all fields
    $sql = "INSERT INTO payment_attempts (
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
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, NOW())";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    
    // Determine null values for SQL
    $null = null;
    
    // Bind all parameters
    $stmt->bind_param(
        "ssssdssssss", 
        $registration_id, 
        $registration_type,
        $status, 
        $payment_id_param, 
        $amount_param, 
        $error_message_param, 
        $ip, 
        $transaction_reference_param,
        $payment_method_param,
        $payment_processor,
        $response_data_param
    );
    
    // Handle null parameters properly
    $payment_id_param = $payment_id ?? $null;
    $amount_param = $amount ?? $null;
    $error_message_param = $error_message ?? $null;
    $transaction_reference_param = $transaction_reference ?? $null;
    $payment_method_param = $payment_method ?? $null;
    $response_data_param = $response_data ?? $null;
    
    if ($stmt->execute()) {
        // Log success for debugging
        error_log("Payment attempt recorded successfully: Registration ID: $registration_id, Status: $status, Payment ID: " . ($payment_id ?? 'NULL') . ", IP: $ip");
        
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
