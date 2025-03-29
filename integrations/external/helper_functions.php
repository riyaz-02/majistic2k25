<?php
/**
 * Helper function to update check-in status by JIS ID
 * Searches in both students and alumni tables
 */
function updateCheckInByJisId($jis_id, $day, $status, $timestamp, $updated_by) {
    global $db;
    
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
    $by_field = $checkin_field . '_by';
    
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
            return [
                'success' => false,
                'message' => 'No paid registration found with JIS ID: ' . $jis_id
            ];
        }
    } else {
        $table = 'registrations';
    }
    
    // Check if ticket is generated
    $checkTicket = $db->prepare("SELECT ticket_generated FROM $table WHERE id = :id");
    $checkTicket->bindParam(':id', $student['id']);
    $checkTicket->execute();
    $ticketStatus = $checkTicket->fetchColumn();
    
    if ($ticketStatus !== 'Yes' && $status === 'Yes') {
        return [
            'success' => false,
            'message' => 'Cannot check in: Ticket not yet generated'
        ];
    }
    
    // Update the check-in status
    $updateStmt = $db->prepare("UPDATE $table SET 
        $checkin_field = :status, 
        $timestamp_field = :timestamp,
        $by_field = :updated_by
        WHERE id = :id"
    );
    
    $updateStmt->bindParam(':status', $status);
    $updateStmt->bindParam(':timestamp', $mysql_timestamp);
    $updateStmt->bindParam(':updated_by', $updated_by);
    $updateStmt->bindParam(':id', $student['id']);
    $updateStmt->execute();
    
    if ($updateStmt->rowCount() > 0) {
        return [
            'success' => true,
            'details' => [
                'student_id' => $student['id'],
                'name' => $student['name'],
                'department' => $student['department'],
                'table' => $table,
                'day' => $day,
                'status' => $status,
                'timestamp' => $mysql_timestamp
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'No changes made, student may already have this check-in status'
        ];
    }
}

/**
 * Get check-in status for a student by JIS ID
 */
function getCheckInStatusByJisId($jis_id) {
    global $db;
    
    // Try to find in registrations table first
    $stmt = $db->prepare("SELECT 
        id, student_name as name, department, 
        ticket_generated, checkin_1, checkin_1_timestamp, 
        checkin_2, checkin_2_timestamp, payment_status
        FROM registrations WHERE jis_id = :jis_id");
    $stmt->bindParam(':jis_id', $jis_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
        
        if ($student) {
            $student['type'] = 'alumni';
        } else {
            return [
                'success' => false,
                'message' => 'No registration found with JIS ID: ' . $jis_id
            ];
        }
    } else {
        $student['type'] = 'student';
    }
    
    // Convert db values to boolean for API response
    return [
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
}

/**
 * Update ticket generation status by JIS ID
 * @param string $jis_id The JIS ID of the student
 * @param string $updated_by Who/what updated the status
 * @return array Result of the operation
 */
function updateTicketGenerationByJisId($jis_id, $updated_by) {
    global $db;
    
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
            return [
                'success' => false,
                'message' => 'No paid registration found with JIS ID: ' . $jis_id
            ];
        }
    } else {
        $table = 'registrations';
    }
    
    // Check if ticket is already generated
    $checkTicket = $db->prepare("SELECT ticket_generated FROM $table WHERE id = :id");
    $checkTicket->bindParam(':id', $student['id']);
    $checkTicket->execute();
    $ticketStatus = $checkTicket->fetchColumn();
    
    if ($ticketStatus === 'Yes') {
        return [
            'success' => false,
            'message' => 'Ticket already generated for JIS ID: ' . $jis_id
        ];
    }
    
    // Update the ticket generation status
    $updateStmt = $db->prepare("UPDATE $table SET ticket_generated = 'Yes' WHERE id = :id");
    $updateStmt->bindParam(':id', $student['id']);
    $updateStmt->execute();
    
    if ($updateStmt->rowCount() > 0) {
        return [
            'success' => true,
            'message' => 'Ticket generation status updated successfully',
            'details' => [
                'student_id' => $student['id'],
                'name' => $student['name'],
                'department' => $student['department'],
                'table' => $table,
                'jis_id' => $jis_id,
                'updated_by' => $updated_by
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'No changes made to ticket status'
        ];
    }
}

/**
 * Log external API calls for debugging
 */
function logApiCall($method, $endpoint, $data, $response) {
    $log_dir = __DIR__ . '/logs';
    
    // Create log directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/api_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[$timestamp] $method $endpoint\n";
    $log_entry .= "Request: " . json_encode($data) . "\n";
    $log_entry .= "Response: " . json_encode($response) . "\n\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
