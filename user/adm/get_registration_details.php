<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../../includes/db_config.php';

// Log the request for debugging
error_log("Request details: Type: {$_GET['type']}, ID: {$_GET['id']}");

// Set content type before any output
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
    } else {
        echo json_encode(['error' => 'Invalid registration type']);
        exit();
    }

    // Convert MongoDB document to array safely
    $registration_array = [];
    foreach ($registration as $key => $value) {
        if ($value instanceof MongoDB\BSON\UTCDateTime) {
            // Store the date information in a format JavaScript can parse easily
            $date = $value->toDateTime();
            $registration_array[$key] = [
                '$date' => $date->format('c') // ISO 8601 format
            ];
        } else if ($value instanceof MongoDB\BSON\ObjectId) {
            $registration_array[$key] = (string)$value;
        } else {
            $registration_array[$key] = $value;
        }
    }
    
    $response['registration'] = $registration_array;
    
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Error in get_registration_details.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
