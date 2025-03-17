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
