<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the content type to JSON
header('Content-Type: application/json');

// Check if user is logged in with Controller role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Controller') {
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

// Convert string ID to MongoDB ObjectId
try {
    $objectId = new MongoDB\BSON\ObjectId($id);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID format']);
    exit;
}

// Select collection based on type
$collection = $type === 'alumni' ? $db->alumni_registrations : $db->registrations;

// Check if the student exists and has paid
$student = $collection->findOne([
    '_id' => $objectId,
    'payment_status' => 'Paid'
]);

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found or payment not complete']);
    exit;
}

// Check if ticket is already generated
if (isset($student['ticket']) && $student['ticket'] === 'generated') {
    echo json_encode(['success' => false, 'message' => 'Ticket already generated']);
    exit;
}

// Update the database to set ticket as generated
try {
    $result = $collection->updateOne(
        ['_id' => $objectId],
        ['$set' => [
            'ticket' => 'generated',
            'ticket_generated_at' => new MongoDB\BSON\UTCDateTime(),
            'ticket_generated_by' => $_SESSION['admin_username']
        ]]
    );
    
    if ($result->getModifiedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Ticket successfully generated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
