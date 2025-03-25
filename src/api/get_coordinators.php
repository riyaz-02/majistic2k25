<?php
require_once '../../includes/db_config.php';

header('Content-Type: application/json');

// Check if we need a specific department
$department = isset($_GET['department']) ? $_GET['department'] : null;

try {
    $coordinators = [];
    
    if ($department) {
        // Use STRICT equality with BINARY for case sensitivity
        // This ensures only EXACT matches are returned
        $query = "SELECT id, department, name, contact, available_time FROM department_coordinators 
                  WHERE BINARY department = BINARY :department ORDER BY department ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':department' => $department]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $coordinators[] = [
                'id' => $row['id'],
                'department' => $row['department'],
                'name' => $row['name'],
                'contact' => $row['contact'],
                'available_time' => $row['available_time']
            ];
        }
        
        // If no results, get all coordinators only if explicitly requested
        if (empty($coordinators) && isset($_GET['fallback']) && $_GET['fallback'] === 'true') {
            $query = "SELECT id, department, name, contact, available_time FROM department_coordinators 
                      ORDER BY department ASC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as $row) {
                $coordinators[] = [
                    'id' => $row['id'],
                    'department' => $row['department'],
                    'name' => $row['name'],
                    'contact' => $row['contact'],
                    'available_time' => $row['available_time']
                ];
            }
        }
    } else {
        // Get all coordinators
        $query = "SELECT id, department, name, contact, available_time FROM department_coordinators 
                  ORDER BY department ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $coordinators[] = [
                'id' => $row['id'],
                'department' => $row['department'],
                'name' => $row['name'],
                'contact' => $row['contact'],
                'available_time' => $row['available_time']
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($coordinators),
        'data' => $coordinators
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve department coordinators: ' . $e->getMessage()
    ]);
}
