<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../../includes/db_config.php';

header('Content-Type: application/json');

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($type) || empty($id)) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

try {
    $response = [];
    
    if ($type === 'inhouse') {
        $registration = $registrations->findOne(['jis_id' => $id]);
        if (!$registration) {
            echo json_encode(['error' => 'Student registration not found']);
            exit();
        }
    } else if ($type === 'alumni') {
        $registration = $alumni_registrations->findOne(['jis_id' => $id]);
        if (!$registration) {
            echo json_encode(['error' => 'Alumni registration not found']);
            exit();
        }
    }

    // Convert MongoDB document to array and handle ObjectId
    $response['registration'] = json_decode(json_encode($registration), true);
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_registration_details.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
