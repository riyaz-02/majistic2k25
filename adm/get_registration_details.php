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

$response = [];

// Get registration details based on type
if ($type === 'inhouse') {
    // Fetch inhouse registration
    $registration_query = "SELECT * FROM registrations WHERE jis_id = ?";
    $stmt = $conn->prepare($registration_query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Registration not found']);
        exit();
    }
    
    $response['registration'] = $result->fetch_assoc();
    
    // Fetch payment attempts
    $payment_query = "SELECT * FROM payment_attempts WHERE user_id = ? AND registration_type = 'inhouse' ORDER BY attempt_time DESC";
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
    // Fetch alumni registration
    $registration_query = "SELECT * FROM registrations_alumni WHERE email = ?";
    $stmt = $conn->prepare($registration_query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Registration not found']);
        exit();
    }
    
    $response['registration'] = $result->fetch_assoc();
    
    // Fetch payment attempts
    $payment_query = "SELECT * FROM payment_attempts WHERE user_id = ? AND registration_type = 'alumni' ORDER BY attempt_time DESC";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $payment_result = $stmt->get_result();
    
    $payment_attempts = [];
    while ($row = $payment_result->fetch_assoc()) {
        $payment_attempts[] = $row;
    }
    
    $response['payment_attempts'] = $payment_attempts;
    
} else {
    // Fetch outhouse registration
    $registration_query = "SELECT * FROM registrations_outhouse WHERE college_id = ?";
    $stmt = $conn->prepare($registration_query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Registration not found']);
        exit();
    }
    
    $response['registration'] = $result->fetch_assoc();
    
    // Fetch payment attempts
    $payment_query = "SELECT * FROM payment_attempts WHERE user_id = ? AND registration_type = 'outhouse' ORDER BY attempt_time DESC";
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
?>
