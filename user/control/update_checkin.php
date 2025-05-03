<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in with Controller or Super Admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || ($_SESSION['admin_role'] !== 'Controller' && $_SESSION['admin_role'] !== 'Super Admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Disabled check-in functionality
echo json_encode(['success' => false, 'message' => 'Check-in functionality is currently disabled']);
exit;

// The rest of the code below will not be executed due to the exit above
require_once __DIR__ . '/../../includes/db_config.php';

// Check if required parameters are provided
if (!isset($_POST['id']) || !isset($_POST['type']) || !isset($_POST['day'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$id = $_POST['id'];
$type = $_POST['type'];
$day = (int)$_POST['day'];

// Validate day parameter
if ($day !== 1 && $day !== 2) {
    echo json_encode(['success' => false, 'message' => 'Invalid day parameter']);
    exit;
}

// Choose the table based on student type
$table = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';
$field = 'checkin_' . $day;

try {
    // First check if student exists and has paid
    $stmt = $db->prepare("SELECT * FROM $table WHERE id = :id AND payment_status = 'Paid'");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found or payment not complete']);
        exit;
    }
    
    // Check if already checked in for the specified day
    if (isset($student[$field]) && $student[$field] === 'Yes') {
        echo json_encode(['success' => false, 'message' => 'Already checked in for day ' . $day]);
        exit;
    }
    
    // Check if ticket is generated before allowing check-in
    if (!isset($student['ticket_generated']) || $student['ticket_generated'] !== 'Yes') {
        echo json_encode(['success' => false, 'message' => 'Cannot check in: Ticket not generated yet']);
        exit;
    }
    
    // Set timezone to UTC for consistent timestamp storage
    date_default_timezone_set('UTC');
    
    // Update the check-in status with current timestamp in UTC
    $currentTime = date('Y-m-d H:i:s');
    $timestampField = $field . '_timestamp';
    
    $updateStmt = $db->prepare("UPDATE $table SET $field = 'Yes', $timestampField = :time WHERE id = :id");
    $updateStmt->bindParam(':time', $currentTime);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $updateStmt->execute();
    
    if ($updateStmt->rowCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Day ' . $day . ' check-in successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
