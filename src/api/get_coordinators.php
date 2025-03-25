<?php
require_once '../../includes/db_config.php';

header('Content-Type: application/json');

// Check if we need a specific department
$department = isset($_GET['department']) ? $_GET['department'] : null;

try {
    $coordinators = [];
    
    if ($department) {
        // Try to find coordinators for the specified department (using LIKE for partial matching)
        $query = "SELECT id, department, name, contact, available_time FROM department_coordinators 
                  WHERE department LIKE :department ORDER BY department ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':department' => "%$department%"]);
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
        
        // If still no results, get all coordinators
        if (empty($coordinators)) {
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
