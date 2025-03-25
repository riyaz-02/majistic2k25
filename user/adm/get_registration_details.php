<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../../includes/db_config.php';

header('Content-Type: application/json');

// Validate input parameters
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$jis_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($type) || empty($jis_id)) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Determine the table based on the type
$table = $type === 'inhouse' ? 'registrations' : ($type === 'alumni' ? 'alumni_registrations' : '');

if (empty($table)) {
    echo json_encode(['error' => 'Invalid registration type']);
    exit();
}

try {
    // Fetch registration details
    $query = "SELECT * FROM $table WHERE jis_id = :jis_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':jis_id' => $jis_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        echo json_encode(['error' => 'Registration not found']);
        exit();
    }

    // Include payment_update_timestamp in the response
    $registration['payment_update_timestamp'] = $registration['payment_update_timestamp'] ?? null;

    // Return the registration details
    echo json_encode(['registration' => $registration]);
} catch (PDOException $e) {
    error_log("Database error in get_registration_details.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
