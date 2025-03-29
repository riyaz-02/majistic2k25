<?php
// Using the secure API key you generated
if (php_sapi_name() != 'cli' && (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== 'd2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f')) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Include database configuration
require_once __DIR__ . '/../../includes/db_config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Ensure we can read the input
$json_data = file_get_contents('php://input');
if ($json_data === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot read input data'
    ]);
    exit;
}

// Log raw input for debugging
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}
file_put_contents($log_dir . '/raw_input_' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . " - " . $json_data . "\n", FILE_APPEND);

// Process webhook request for check-ins
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON data
    $data = json_decode($json_data, true);
    
    // Log the incoming webhook data
    logApiCall('WEBHOOK', 'POST', $data);
    
    // Normalize field names (accept both formats)
    // Support both "day" and "checkin_day" formats, and "status" and "checkin_status" formats
    if (isset($data['checkin_day']) && !isset($data['day'])) {
        $data['day'] = $data['checkin_day'];
    }
    
    if (isset($data['checkin_status']) && !isset($data['status'])) {
        $data['status'] = $data['checkin_status'];
    }
    
    // Validate webhook payload
    if (!$data || !isset($data['jis_id']) || !isset($data['day']) || !isset($data['status'])) {
        $response = [
            'success' => false,
            'message' => 'Invalid webhook payload. Required fields: jis_id, day/checkin_day, status/checkin_status'
        ];
        logApiCall('WEBHOOK', 'RESPONSE', $response);
        echo json_encode($response);
        exit;
    }
    
    $jis_id = $data['jis_id'];
    $day = intval($data['day']);
    $status = ($data['status'] === true || $data['status'] === 'Yes' || $data['status'] === 1) ? 'Yes' : 'No';
    $timestamp = isset($data['timestamp']) ? $data['timestamp'] : date('c');
    
    // Validate day parameter
    if ($day !== 1 && $day !== 2) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid day parameter. Must be 1 or 2.'
        ]);
        exit;
    }
    
    try {
        // Convert ISO timestamp to MySQL format if needed
        if (strpos($timestamp, 'T') !== false) {
            $date = new DateTime($timestamp);
            $mysql_timestamp = $date->format('Y-m-d H:i:s');
        } else {
            $mysql_timestamp = $timestamp;
        }
        
        // Define fields to update
        $checkin_field = 'checkin_' . $day;
        $timestamp_field = $checkin_field . '_timestamp';
        
        // First try to find the student in the registrations table
        $stmt = $db->prepare("SELECT id FROM registrations WHERE jis_id = :jis_id AND payment_status = 'Paid'");
        $stmt->bindParam(':jis_id', $jis_id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            $table = 'registrations';
            $id = $student['id'];
        } else {
            // If not found in students, try alumni
            $stmt = $db->prepare("SELECT id FROM alumni_registrations WHERE jis_id = :jis_id AND payment_status = 'Paid'");
            $stmt->bindParam(':jis_id', $jis_id);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                $table = 'alumni_registrations';
                $id = $student['id'];
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No paid registration found with JIS ID: ' . $jis_id
                ]);
                exit;
            }
        }
        
        // Update the check-in status and timestamp only
        $updateStmt = $db->prepare("UPDATE $table SET 
            $checkin_field = :status, 
            $timestamp_field = :timestamp
            WHERE id = :id"
        );
        
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':timestamp', $mysql_timestamp);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->execute();
        
        if ($updateStmt->rowCount() > 0) {
            $successResponse = [
                'success' => true,
                'message' => 'Check-in status updated successfully',
                'details' => [
                    'jis_id' => $jis_id,
                    'day' => $day,
                    'status' => $status,
                    'timestamp' => $mysql_timestamp
                ]
            ];
            logApiCall('WEBHOOK', 'SUCCESS', $successResponse);
            echo json_encode($successResponse);
        } else {
            $noChangeResponse = [
                'success' => true, // Still return success but with a note
                'message' => 'No changes made, student may already have this check-in status',
                'details' => [
                    'jis_id' => $jis_id,
                    'day' => $day,
                    'status' => $status
                ]
            ];
            logApiCall('WEBHOOK', 'NO_CHANGE', $noChangeResponse);
            echo json_encode($noChangeResponse);
        }
    } catch (Exception $e) {
        $errorMsg = 'Database error occurred: ' . $e->getMessage();
        logApiCall('WEBHOOK', 'ERROR', $errorMsg);
        echo json_encode([
            'success' => false,
            'message' => $errorMsg
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method, only POST is supported'
    ]);
}

/**
 * Simple function to log API calls
 */
function logApiCall($type, $action, $data) {
    $log_dir = __DIR__ . '/logs';
    
    // Create log directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/webhook_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp] $type $action: " . json_encode($data) . "\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
