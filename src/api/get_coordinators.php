<?php
require_once '../../includes/db_config.php';

header('Content-Type: application/json');

// Check if we need a specific department
$department = isset($_GET['department']) ? $_GET['department'] : null;

try {
    $filter = [];
    if ($department) {
        // Case-insensitive search for department
        $filter = ['department' => ['$regex' => $department, '$options' => 'i']];
    }
    
    // Get all coordinators, sorted by department name
    $cursor = $department_coordinators->find($filter, ['sort' => ['department' => 1]]);
    
    $coordinators = [];
    foreach ($cursor as $doc) {
        $coordinators[] = [
            'id' => (string)$doc->_id,
            'department' => $doc->department,
            'name' => $doc->name,
            'contact' => $doc->contact,
            'available_time' => isset($doc->available_time) ? $doc->available_time : null
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($coordinators),
        'data' => $coordinators
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve department coordinators: ' . $e->getMessage()
    ]);
}
