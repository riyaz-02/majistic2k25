<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Create a log function
function logError($message) {
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/error_log_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Disable direct browser access with your secure API key
    if (php_sapi_name() != 'cli' && (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== 'd2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f')) {
        header('HTTP/1.0 403 Forbidden');
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Include database configuration
    if (!file_exists(__DIR__ . '/../../includes/db_config.php')) {
        logError("Database config file not found");
        throw new Exception("Database configuration file not found");
    }
    
    require_once __DIR__ . '/../../includes/db_config.php';

    // Set content type to JSON
    header('Content-Type: application/json');
    
    // Log request details
    logError("Request method: " . $_SERVER['REQUEST_METHOD'] . ", JIS ID: " . ($_GET['jis_id'] ?? 'not provided'));
    
    // Process GET request for status check
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['jis_id'])) {
        $jis_id = $_GET['jis_id'];
        
        try {
            // Try to find in registrations table first
            $stmt = $db->prepare("SELECT 
                id, student_name as name, department, 
                ticket_generated, checkin_1, checkin_1_timestamp, 
                checkin_2, checkin_2_timestamp, payment_status
                FROM registrations WHERE jis_id = :jis_id");
            $stmt->bindParam(':jis_id', $jis_id);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            logError("Student search result: " . ($student ? json_encode($student) : "Not found in registrations"));
            
            // If not found, try alumni table
            if (!$student) {
                $stmt = $db->prepare("SELECT 
                    id, alumni_name as name, department, 
                    ticket_generated, checkin_1, checkin_1_timestamp, 
                    checkin_2, checkin_2_timestamp, payment_status
                    FROM alumni_registrations WHERE jis_id = :jis_id");
                $stmt->bindParam(':jis_id', $jis_id);
                $stmt->execute();
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                logError("Alumni search result: " . ($student ? json_encode($student) : "Not found in alumni"));
                
                if ($student) {
                    $student['type'] = 'alumni';
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No registration found with JIS ID: ' . $jis_id
                    ]);
                    exit;
                }
            } else {
                $student['type'] = 'student';
            }
            
            // Convert db values to boolean for API response
            $response = [
                'success' => true,
                'jis_id' => $jis_id,
                'name' => $student['name'],
                'department' => $student['department'],
                'type' => $student['type'],
                'is_paid' => $student['payment_status'] === 'Paid',
                'ticket_generated' => $student['ticket_generated'] === 'Yes',
                'check_in_status' => [
                    'day1' => [
                        'checked_in' => $student['checkin_1'] === 'Yes',
                        'timestamp' => $student['checkin_1_timestamp']
                    ],
                    'day2' => [
                        'checked_in' => $student['checkin_2'] === 'Yes',
                        'timestamp' => $student['checkin_2_timestamp']
                    ]
                ]
            ];
            
            logError("Success response: " . json_encode($response));
            echo json_encode($response);
        } catch (PDOException $e) {
            logError("PDO Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Process POST request for check-in updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get JSON payload
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        // Validate input
        if (!$data || 
            !isset($data['jis_id']) || 
            !isset($data['checkin_day']) || 
            !isset($data['checkin_status']) ||
            !isset($data['timestamp'])) {
            
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input data'
            ]);
            exit;
        }
        
        $jis_id = $data['jis_id'];
        $day = intval($data['checkin_day']);
        $status = $data['checkin_status'] === true ? 'Yes' : 'No';
        $timestamp = $data['timestamp']; // ISO format: 2023-04-15T14:30:00Z
        
        // Validate day (must be 1 or 2)
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
            $stmt = $db->prepare("SELECT id, student_name as name, department FROM registrations WHERE jis_id = :jis_id AND payment_status = 'Paid'");
            $stmt->bindParam(':jis_id', $jis_id);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If not found in students, try alumni
            if (!$student) {
                $stmt = $db->prepare("SELECT id, alumni_name as name, department FROM alumni_registrations WHERE jis_id = :jis_id AND payment_status = 'Paid'");
                $stmt->bindParam(':jis_id', $jis_id);
                $stmt->execute();
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($student) {
                    $table = 'alumni_registrations';
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No paid registration found with JIS ID: ' . $jis_id
                    ]);
                    exit;
                }
            } else {
                $table = 'registrations';
            }
            
            // Update the check-in status
            $updateStmt = $db->prepare("UPDATE $table SET 
                $checkin_field = :status, 
                $timestamp_field = :timestamp
                WHERE id = :id"
            );
            
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':timestamp', $mysql_timestamp);
            $updateStmt->bindParam(':id', $student['id']);
            $updateStmt->execute();
            
            if ($updateStmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Check-in updated successfully',
                    'details' => [
                        'student_id' => $student['id'],
                        'name' => $student['name'],
                        'department' => $student['department'],
                        'table' => $table,
                        'day' => $day,
                        'status' => $status,
                        'timestamp' => $mysql_timestamp
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No changes made, student may already have this check-in status'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
        exit;
    } 

    // Handle invalid requests
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing parameters'
    ]);
} catch (Exception $e) {
    // Log the full error stack
    logError("CRITICAL ERROR: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    
    // Send a structured error response
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred. Please check the logs for details.',
        'error' => $e->getMessage()
    ]);
}
?>
