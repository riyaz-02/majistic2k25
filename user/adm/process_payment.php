<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../../includes/db_config.php';
require_once __DIR__ . '/email_sender.php'; // Include the email utility

// Get raw input data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

header('Content-Type: application/json');

// Validate input data
if (!isset($data['type']) || !isset($data['jis_id']) || !isset($data['receipt_number']) || !isset($data['amount'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

$type = $data['type'];
$jis_id = $data['jis_id'];
$receipt_number = $data['receipt_number'];
$amount = $data['amount'];

// Get the admin user's information
$admin_username = $_SESSION['admin_username'] ?? 'Unknown';
$admin_id = $_SESSION['admin_id'] ?? '';

try {
    // Determine which collection to update based on type
    $collection = ($type === 'inhouse') ? $registrations : $alumni_registrations;
    
    // Find the registration to ensure it exists and is not already paid
    $registration = $collection->findOne(['jis_id' => $jis_id]);
    
    if (!$registration) {
        echo json_encode(['success' => false, 'error' => 'Registration not found']);
        exit();
    }
    
    if (isset($registration['payment_status']) && $registration['payment_status'] === 'Paid') {
        echo json_encode(['success' => false, 'error' => 'Payment has already been processed for this registration']);
        exit();
    }
    
    // Current timestamp for payment
    $payment_timestamp = new MongoDB\BSON\UTCDateTime();
    
    // Update payment information
    $updateResult = $collection->updateOne(
        ['jis_id' => $jis_id],
        [
            '$set' => [
                'payment_status' => 'Paid',
                'payment_amount' => $amount,
                'receipt_number' => $receipt_number,
                'payment_updated_by' => $admin_username,
                'payment_updated_by_id' => $admin_id,
                'payment_timestamp' => $payment_timestamp
            ]
        ]
    );
    
    if ($updateResult->getModifiedCount() > 0) {
        // Extract information needed for the email
        $emailData = [
            'type' => $type,
            'receipt_number' => $receipt_number,
            'amount' => $amount,
            'payment_date' => date('d-m-Y h:i A')
        ];
        
        // Add registration-specific data
        if ($type === 'inhouse') {
            $emailData['name'] = $registration['student_name'] ?? 'Student';
            $emailData['email'] = $registration['email'] ?? '';
            $emailData['jis_id'] = $registration['jis_id'] ?? '';
            $emailData['department'] = $registration['department'] ?? '';
            $emailData['competition'] = $registration['competition_name'] ?? '';
        } else {
            $emailData['name'] = $registration['alumni_name'] ?? 'Alumni';
            $emailData['email'] = $registration['email'] ?? '';
            $emailData['jis_id'] = $registration['jis_id'] ?? '';
            $emailData['department'] = $registration['department'] ?? '';
            $emailData['passout_year'] = $registration['passout_year'] ?? '';
        }
        
        // Send confirmation email
        $emailSent = sendPaymentConfirmationEmail($emailData);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully',
            'email_sent' => $emailSent
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update payment information']);
    }
} catch (Exception $e) {
    error_log("Error in process_payment.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
