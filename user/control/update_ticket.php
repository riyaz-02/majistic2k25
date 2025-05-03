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

require_once __DIR__ . '/../../includes/db_config.php';

// Check if ID is provided
if (!isset($_POST['id']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID or type']);
    exit;
}

$id = $_POST['id'];
$type = $_POST['type'];

// Choose the table based on student type
$table = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';

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
    
    // Check if ticket is already generated
    if (isset($student['ticket_generated']) && $student['ticket_generated'] === 'Yes') {
        echo json_encode(['success' => false, 'message' => 'Ticket already generated']);
        exit;
    }
    
    // Update only the ticket_generated status
    $updateStmt = $db->prepare("UPDATE $table SET ticket_generated = 'Yes' WHERE id = :id");
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $updateStmt->execute();
    
    if ($updateStmt->rowCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Ticket successfully generated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
