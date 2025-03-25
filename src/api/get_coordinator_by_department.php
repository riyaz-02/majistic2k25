<?php
header('Content-Type: application/json');
require_once '../../includes/db_config.php';

// Get department from request
$department = isset($_GET['department']) ? trim($_GET['department']) : '';

if (empty($department)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Department parameter is required'
    ]);
    exit;
}

try {
    // Use STRICT equality with = operator and BINARY for case sensitivity
    // This ensures only EXACT matches are returned
    $query = "SELECT name, contact, available_time FROM department_coordinators WHERE BINARY department = BINARY :department ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([':department' => $department]);
    $coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($coordinators)) {
        echo json_encode([
            'status' => 'success',
            'count' => count($coordinators),
            'coordinators' => $coordinators
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No coordinator found for this department',
            'fallbackMessage' => 'Please contact your department office or reach out to maJIStic Support for assistance.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
