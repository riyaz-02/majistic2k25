<?php
include '../../includes/db_config.php';

// Get parameters from POST request
$jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
$is_alumni = isset($_POST['alumni']) && $_POST['alumni'] == '1';

// Validate required parameters
if (empty($jis_id)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'JIS ID is required'
    ]);
    exit;
}

// Log the request
error_log("Check payment status - JIS ID: $jis_id, Is Alumni: " . ($is_alumni ? "Yes" : "No"));

try {
    // Add code to record the payment status check in payment_attempts table
    try {
        // Get the client IP address - enhanced version
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
        $status = 'check'; // Use 'check' for status checks
        $payment_method = 'status_check';
        $registration_type = $is_alumni ? 'alumni' : 'inhouse';
        
        // Record in payment_attempts table with registration_type
        $record_stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, registration_type, status, attempt_time, ip_address, payment_method) VALUES (?, ?, ?, NOW(), ?, ?)");
        $record_stmt->bind_param("sssss", $jis_id, $registration_type, $status, $ip, $payment_method);
        $record_stmt->execute();
        $record_stmt->close();
        
        error_log("Payment status check recorded for JIS ID: $jis_id, Is Alumni: " . ($is_alumni ? "Yes" : "No") . ", IP: $ip");
    } catch (Exception $e) {
        // Non-critical error, just log it and continue with the main operation
        error_log("Failed to record payment status check: " . $e->getMessage());
    }

    if ($is_alumni) {
        // Check alumni registration status
        $query = $conn->prepare("SELECT payment_status, alumni_name, registration_date FROM alumni_registrations WHERE jis_id = ?");
        $query->bind_param("s", $jis_id);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No alumni registration found with this JIS ID'
            ]);
            exit;
        }
        
        $registration = $result->fetch_assoc();
        
        if ($registration['payment_status'] == 'Not Paid') {
            echo json_encode([
                'status' => 'pending',
                'message' => 'Payment pending for ' . $registration['alumni_name'],
                'registration_date' => $registration['registration_date']
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment already completed for ' . $registration['alumni_name']
            ]);
        }
    } else {
        // Check regular student registration status
        $query = $conn->prepare("SELECT payment_status, student_name, registration_date FROM registrations WHERE jis_id = ?");
        $query->bind_param("s", $jis_id);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No registration found with this JIS ID'
            ]);
            exit;
        }
        
        $registration = $result->fetch_assoc();
        
        if ($registration['payment_status'] == 'Not Paid') {
            echo json_encode([
                'status' => 'pending',
                'message' => 'Payment pending for ' . $registration['student_name'],
                'registration_date' => $registration['registration_date']
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment already completed for ' . $registration['student_name']
            ]);
        }
    }
    
    $query->close();
} catch (Exception $e) {
    http_response_code(500);
    error_log("Payment status check error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while checking payment status'
    ]);
}

$conn->close();
?>
