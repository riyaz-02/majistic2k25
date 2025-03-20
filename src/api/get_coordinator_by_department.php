<?php
require_once '../../includes/db_config.php';

header('Content-Type: application/json');

// Check if department parameter is provided
if (!isset($_GET['department']) || empty($_GET['department'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Department parameter is required'
    ]);
    exit;
}

$department = $_GET['department'];

try {
    // Case-insensitive search for department
    $coordinator = $department_coordinators->findOne([
        'department' => ['$regex' => $department, '$options' => 'i']
    ]);
    
    if ($coordinator) {
        // Format the coordinator data
        $coordinator_data = [
            'id' => (string)$coordinator->_id,
            'department' => $coordinator->department,
            'name' => $coordinator->name,
            'contact' => $coordinator->contact,
            'available_time' => isset($coordinator->available_time) ? $coordinator->available_time : null,
            'email' => isset($coordinator->email) ? $coordinator->email : null
        ];
        
        echo json_encode([
            'status' => 'success',
            'data' => $coordinator_data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No coordinator found for the specified department'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
