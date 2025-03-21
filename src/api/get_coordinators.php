<?php
require_once '../../includes/db_config.php';

header('Content-Type: application/json');

// Check if we need a specific department
$department = isset($_GET['department']) ? $_GET['department'] : null;

try {
    $filter = [];
    $coordinators = [];
    
    if ($department) {
        // Try exact match first (case-insensitive)
        $exactMatch = $department_coordinators->findOne([
            'department' => ['$regex' => '^' . preg_quote($department) . '$', '$options' => 'i']
        ]);
        
        if ($exactMatch) {
            // If exact match found, return only that coordinator
            $coordinators[] = [
                'id' => (string)$exactMatch->_id,
                'department' => $exactMatch->department,
                'name' => $exactMatch->name,
                'contact' => $exactMatch->contact,
                'available_time' => isset($exactMatch->available_time) ? $exactMatch->available_time : null
            ];
        } else {
            // If no exact match, try another approach - check for department code
            // This handles cases like "ME" vs "Mechanical Engineering"
            $cursor = $department_coordinators->find([
                '$or' => [
                    ['department_code' => ['$regex' => '^' . preg_quote($department) . '$', '$options' => 'i']],
                    ['department_aliases' => ['$in' => [$department]]]
                ]
            ]);
            
            foreach ($cursor as $doc) {
                $coordinators[] = [
                    'id' => (string)$doc->_id,
                    'department' => $doc->department,
                    'name' => $doc->name,
                    'contact' => $doc->contact,
                    'available_time' => isset($doc->available_time) ? $doc->available_time : null
                ];
            }
        }
    } else {
        // If no department specified, get all coordinators
        $cursor = $department_coordinators->find([], ['sort' => ['department' => 1]]);
        
        foreach ($cursor as $doc) {
            $coordinators[] = [
                'id' => (string)$doc->_id,
                'department' => $doc->department,
                'name' => $doc->name,
                'contact' => $doc->contact,
                'available_time' => isset($doc->available_time) ? $doc->available_time : null
            ];
        }
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
