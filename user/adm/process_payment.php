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
require_once __DIR__ . '/email_sender.php'; // Include email sender

header('Content-Type: application/json');

// Validate input data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Debugging: Log the received input
error_log("Debug: Received input: " . json_encode($input));

$type = isset($input['type']) ? trim($input['type']) : '';
$jis_id = isset($input['id']) ? trim($input['id']) : ''; // Use 'id' as 'jis_id'
$receipt_number = isset($input['receipt_number']) ? trim($input['receipt_number']) : '';
$paid_amount = isset($input['paid_amount']) ? (float)$input['paid_amount'] : 0;

// Debugging: Log parsed parameters
error_log("Debug: Parsed Parameters - Type: $type, JIS ID: $jis_id, Receipt Number: $receipt_number, Paid Amount: $paid_amount");

// Check for missing or invalid parameters
if (empty($type) || !in_array($type, ['student', 'alumni'])) {
    error_log("Debug: Missing or invalid parameter: type");
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameter: type']);
    exit();
}

if (empty($jis_id)) {
    error_log("Debug: Missing or invalid parameter: jis_id");
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameter: jis_id']);
    exit();
}

if (empty($receipt_number)) {
    error_log("Debug: Missing or invalid parameter: receipt_number");
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameter: receipt_number']);
    exit();
}

if ($paid_amount <= 0) {
    error_log("Debug: Missing or invalid parameter: paid_amount");
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameter: paid_amount']);
    exit();
}

// Determine the table based on registration type
$table = $type === 'student' ? 'registrations' : 'alumni_registrations';

try {
    // Check if the registration exists and is not already paid
    $query = "SELECT * FROM $table WHERE jis_id = :jis_id LIMIT 1"; // Use 'jis_id' for lookup
    $stmt = $db->prepare($query);
    $stmt->execute([':jis_id' => $jis_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        error_log("Debug: Registration not found for JIS ID: $jis_id in table: $table");
        echo json_encode(['success' => false, 'error' => 'Registration not found']);
        exit();
    }

    if ($registration['payment_status'] === 'Paid') {
        error_log("Debug: Payment already processed for JIS ID: $jis_id");
        echo json_encode(['success' => false, 'error' => 'Payment has already been processed for this registration']);
        exit();
    }

    // Update payment information
    $query = "UPDATE $table SET 
                payment_status = 'Paid',
                paid_amount = :paid_amount,
                receipt_number = :receipt_number,
                payment_updated_by = :updated_by,
                payment_update_timestamp = NOW()
              WHERE jis_id = :jis_id"; // Use 'jis_id' for update
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':paid_amount' => $paid_amount,
        ':receipt_number' => $receipt_number,
        ':updated_by' => $_SESSION['admin_username'],
        ':jis_id' => $jis_id
    ]);

    if ($stmt->rowCount() > 0) {
        error_log("Debug: Payment updated successfully for JIS ID: $jis_id");

        // Send payment confirmation email
        $email_data = [
            'email' => $registration['email'],
            'name' => $type === 'student' ? $registration['student_name'] : $registration['alumni_name'],
            'jis_id' => $registration['jis_id'],
            'department' => $registration['department'],
            'amount' => $paid_amount,
            'receipt_number' => $receipt_number,
            'payment_date' => date('Y-m-d H:i:s'),
            'type' => $type,
        ];

        if ($type === 'student') {
            $email_data['competition'] = $registration['competition_name'] ?? 'N/A';
        } else {
            $email_data['passout_year'] = $registration['passout_year'] ?? 'N/A';
        }

        $email_sent = sendPaymentConfirmationEmail($email_data);

        if ($email_sent) {
            error_log("Debug: Email sent successfully for JIS ID: $jis_id");
            echo json_encode(['success' => true, 'message' => 'Payment updated and email sent successfully']);
        } else {
            error_log("Debug: Email could not be sent for JIS ID: $jis_id");
            echo json_encode(['success' => true, 'message' => 'Payment updated successfully, but email could not be sent']);
        }
    } else {
        error_log("Debug: Failed to update payment information for JIS ID: $jis_id");
        echo json_encode(['success' => false, 'error' => 'Failed to update payment information']);
    }
} catch (PDOException $e) {
    error_log("Debug: Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
