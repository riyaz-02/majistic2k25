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

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validate input
if (empty($type) || empty($id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Initialize response array
$response = [];

try {
    if ($type === 'inhouse') {
        // Get inhouse registration details
        $query = "SELECT * FROM registrations WHERE jis_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Registration not found");
        }
        
        $response['registration'] = $result->fetch_assoc();
        
        // Get payment attempts
        $payment_query = "SELECT * FROM payment_attempts WHERE registration_id = ? ORDER BY attempt_time DESC";
        $payment_stmt = $conn->prepare($payment_query);
        $payment_stmt->bind_param("s", $id);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();
        
        $payment_attempts = [];
        while ($row = $payment_result->fetch_assoc()) {
            $payment_attempts[] = $row;
        }
        
        $response['payment_attempts'] = $payment_attempts;
        
    } elseif ($type === 'outhouse') {
        // Get outhouse registration details
        $query = "SELECT * FROM registrations_outhouse WHERE college_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Registration not found");
        }
        
        $response['registration'] = $result->fetch_assoc();
        
        // Get payment attempts
        $payment_query = "SELECT * FROM payment_attempts WHERE registration_id = ? ORDER BY attempt_time DESC";
        $payment_stmt = $conn->prepare($payment_query);
        $payment_stmt->bind_param("s", $id);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();
        
        $payment_attempts = [];
        while ($row = $payment_result->fetch_assoc()) {
            $payment_attempts[] = $row;
        }
        
        $response['payment_attempts'] = $payment_attempts;
        
    } else {
        throw new Exception("Invalid registration type");
    }
    
    // Set response headers
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
