<?php
include '../../includes/db_config.php';

header('Content-Type: application/json');

$jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
$roll_no = isset($_POST['roll_no']) ? $_POST['roll_no'] : '';

if (empty($jis_id) || empty($roll_no)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'JIS ID and Roll Number are required'
    ]);
    exit;
}

try {
    // Check if registration exists and get payment status
    $stmt = $conn->prepare("
        SELECT 
            payment_status, student_name
        FROM 
            registrations 
        WHERE 
            jis_id = ? AND roll_no = ?
    ");
    $stmt->bind_param("ss", $jis_id, $roll_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No registration found for the provided JIS ID and Roll Number'
        ]);
    } else {
        $registration = $result->fetch_assoc();
        
        if ($registration['payment_status'] == 'Not Paid') {
            echo json_encode([
                'status' => 'pending',
                'message' => 'Payment pending. Redirecting to payment page...'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment already completed for ' . $registration['student_name'] . '. No further action required.'
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Payment status check error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while checking payment status'
    ]);
}

$conn->close();
?>
