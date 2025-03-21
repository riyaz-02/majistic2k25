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

// Check if already checked in for the specified day
$field = 'checkin_' . $day;
if (isset($student[$field]) && $student[$field] === 'checkedin') {
    echo json_encode(['success' => false, 'message' => 'Already checked in for day ' . $day]);
    exit;
}

// Check if ticket is generated before allowing check-in
if (!isset($student['ticket']) || $student['ticket'] !== 'generated') {
    echo json_encode(['success' => false, 'message' => 'Cannot check in: Ticket not generated yet']);
    exit;
}

// Update fields to set in database
$updateFields = [
    $field => 'checkedin',
    $field . '_at' => new MongoDB\BSON\UTCDateTime(),
    $field . '_by' => $_SESSION['admin_username']
];

// Update the database
try {
    $result = $collection->updateOne(
        ['_id' => $objectId],
        ['$set' => $updateFields]
    );
    
    if ($result->getModifiedCount() === 1) {
        echo json_encode(['success' => true, 'message' => 'Day ' . $day . ' check-in successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes were made']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
