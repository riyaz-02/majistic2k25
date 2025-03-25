<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get registration type and ID
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($type) || $id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid registration specified']);
    exit;
}

// Determine the table and fields based on registration type
$table = $type === 'student' ? 'registrations' : 'alumni_registrations';
$name_field = $type === 'student' ? 'student_name' : 'alumni_name';

// Fetch registration details
try {
    $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo json_encode(['status' => 'error', 'message' => 'Registration not found']);
        exit;
    }
    
    // Build HTML content for modal
    $html = '<div class="registration-details">';
    
    // Type indicator
    $html .= '<div class="text-center mb-3">';
    $html .= '<span class="badge bg-' . ($type === 'student' ? 'info' : 'secondary') . ' fs-6 px-3 py-2">' . ucfirst($type) . ' Registration</span>';
    $html .= '</div>';
    
    // Basic Information
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">JIS ID:</label>';
    $html .= '<div>' . htmlspecialchars($registration['jis_id']) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Name:</label>';
    $html .= '<div>' . htmlspecialchars($registration[$name_field]) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Second Row
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Department:</label>';
    $html .= '<div>' . htmlspecialchars($registration['department']) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    if ($type === 'student') {
        $html .= '<label class="fw-bold">Gender:</label>';
        $html .= '<div>' . htmlspecialchars($registration['gender']) . '</div>';
    } else {
        $html .= '<label class="fw-bold">Passout Year:</label>';
        $html .= '<div>' . htmlspecialchars($registration['passout_year']) . '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Third Row
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Email:</label>';
    $html .= '<div>' . htmlspecialchars($registration['email']) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Mobile:</label>';
    $html .= '<div>' . htmlspecialchars($registration['mobile']) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Fourth Row - Type specific
    if ($type === 'student' && isset($registration['inhouse_competition'])) {
        $html .= '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<div class="mb-3">';
        $html .= '<label class="fw-bold">In-house Competition:</label>';
        $html .= '<div>' . htmlspecialchars($registration['inhouse_competition']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        if ($registration['inhouse_competition'] === 'Yes' && !empty($registration['competition_name'])) {
            $html .= '<div class="col-md-6">';
            $html .= '<div class="mb-3">';
            $html .= '<label class="fw-bold">Competition:</label>';
            $html .= '<div>' . htmlspecialchars($registration['competition_name']) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
    } elseif ($type === 'alumni' && !empty($registration['current_organization'])) {
        $html .= '<div class="row">';
        $html .= '<div class="col-md-12">';
        $html .= '<div class="mb-3">';
        $html .= '<label class="fw-bold">Current Organization:</label>';
        $html .= '<div>' . htmlspecialchars($registration['current_organization']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // Registration Info
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Registration Date:</label>';
    $html .= '<div>' . date('d M Y, h:i A', strtotime($registration['registration_date'])) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<div class="mb-3">';
    $html .= '<label class="fw-bold">Payment Status:</label>';
    $html .= '<div><span class="badge bg-' . ($registration['payment_status'] === 'Paid' ? 'success' : 'warning') . '">' . $registration['payment_status'] . '</span></div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '</div>'; // End registration-details
    
    echo json_encode(['status' => 'success', 'html' => $html]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
