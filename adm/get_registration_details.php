<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

include '../includes/db_config.php';

// Set headers for JSON response
header('Content-Type: application/json');

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($type) || empty($id)) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Log request details for debugging
error_log("Request Type: $type, ID: $id");

$response = [];

try {
    // Get registration details based on type
    if ($type === 'inhouse') {
        // Fetch inhouse registration
        $registration_query = "SELECT * FROM registrations WHERE jis_id = ?";
        $stmt = $conn->prepare($registration_query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'Inhouse registration not found']);
            exit();
        }
        
        $response['registration'] = $result->fetch_assoc();
        
        // Fetch payment attempts
        $payment_query = "SELECT * FROM payment_attempts WHERE registration_id = ? ORDER BY attempt_time DESC";
        $stmt = $conn->prepare($payment_query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $payment_result = $stmt->get_result();
        
        $payment_attempts = [];
        while ($row = $payment_result->fetch_assoc()) {
            $payment_attempts[] = $row;
        }
        
        $response['payment_attempts'] = $payment_attempts;
        
    } else if ($type === 'alumni') {
        // For debugging purposes, check if the table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'alumni_registrations'");
        if ($table_check->num_rows === 0) {
            echo json_encode(['error' => 'Alumni registrations table does not exist']);
            exit();
        }
        
        // Fetch alumni registration
        $registration_query = "SELECT * FROM alumni_registrations WHERE jis_id = ?";
        $stmt = $conn->prepare($registration_query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['error' => "Alumni registration not found for JIS ID: $id"]);
            exit();
        }
        
        $response['registration'] = $result->fetch_assoc();
        
        // Fetch payment attempts
        $payment_query = "SELECT * FROM payment_attempts WHERE registration_id = ? ORDER BY attempt_time DESC";
        $stmt = $conn->prepare($payment_query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $payment_result = $stmt->get_result();
        
        $payment_attempts = [];
        while ($row = $payment_result->fetch_assoc()) {
            $payment_attempts[] = $row;
        }
        
        $response['payment_attempts'] = $payment_attempts;
    }

    // Return JSON response
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_registration_details.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
