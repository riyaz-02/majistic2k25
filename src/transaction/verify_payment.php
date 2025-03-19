<?php
include '../../includes/db_config.php';

// Get the JIS ID and payment ID from request
$jis_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : '';
$payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : '';
$is_alumni = isset($_GET['alumni']) && $_GET['alumni'] == '1';

// Check if we have the required parameters
if (empty($jis_id) || empty($payment_id)) {
    http_response_code(400);
    echo json_encode([
        'verified' => false,
        'message' => 'JIS ID and Payment ID are required'
    ]);
    exit;
}

try {
    // Get client IP for logging
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
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipList as $ip) {
                if (filter_var(trim($ip), FILTER_VALIDATE_IP)) {
                    return trim($ip);
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }
    
    $ip = getClientIP();
    
    // Log the verification attempt
    $registration_type = $is_alumni ? 'alumni' : 'inhouse';
    
    try {
        $stmt = $conn->prepare("INSERT INTO payment_attempts (
            registration_id, 
            registration_type,
            status,
            payment_id,
            attempt_time,
            ip_address,
            payment_method
        ) VALUES (?, ?, 'verification', ?, NOW(), ?, 'verify_api')");
        
        $stmt->bind_param("ssss", $jis_id, $registration_type, $payment_id, $ip);
        $stmt->execute();
    } catch (Exception $e) {
        // Non-critical error, just log it
        error_log("Failed to log payment verification: " . $e->getMessage());
    }
    
    // Query the appropriate table based on is_alumni flag
    if ($is_alumni) {
        $query = $conn->prepare("SELECT 
            a.alumni_name as name, 
            a.payment_status, 
            a.payment_date, 
            a.amount_paid, 
            a.email, 
            a.department
        FROM alumni_registrations a 
        WHERE a.jis_id = ? AND a.payment_id = ?");
    } else {
        $query = $conn->prepare("SELECT 
            r.student_name as name, 
            r.payment_status, 
            r.payment_date, 
            r.amount_paid, 
            r.email, 
            r.department
        FROM registrations r 
        WHERE r.jis_id = ? AND r.payment_id = ?");
    }
    
    $query->bind_param("ss", $jis_id, $payment_id);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'verified' => false,
            'message' => 'No matching payment record found'
        ]);
        exit;
    }
    
    $payment = $result->fetch_assoc();
    
    // Format payment date
    if (!empty($payment['payment_date'])) {
        $payment_date = date('d M Y, h:i A', strtotime($payment['payment_date']));
    } else {
        $payment_date = 'N/A';
    }
    
    // Build verification response
    $response = [
        'verified' => ($payment['payment_status'] === 'Paid'),
        'jis_id' => $jis_id,
        'name' => $payment['name'],
        'payment_id' => $payment_id,
        'payment_date' => $payment_date,
        'amount' => $payment['amount_paid'],
        'email' => substr($payment['email'], 0, 3) . '...' . strstr($payment['email'], '@'),
        'department' => $payment['department'],
        'registration_type' => $is_alumni ? 'Alumni' : 'Student',
        'verification_time' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'verified' => false,
        'message' => 'An error occurred during verification: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
