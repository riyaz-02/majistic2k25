<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in with Controller role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Controller') {
    // Return error message as JSON
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../../includes/db_config.php';

// Check if ID is provided
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    echo '<div class="alert alert-danger">Missing student ID or type</div>';
    exit;
}

$id = $_GET['id'];
$type = $_GET['type'];

// Choose the table based on student type
$table = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';

try {
    // Fetch student data
    $stmt = $db->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo '<div class="alert alert-danger">Student not found</div>';
        exit;
    }
    
    // Generate status badges
    $paymentStatus = '<span class="badge bg-success">Paid</span>';
    $ticketStatus = isset($student['ticket_generated']) && $student['ticket_generated'] === 'Yes' ? 
        '<span class="badge bg-success">Generated</span>' : 
        '<span class="badge bg-danger">Not Generated</span>';
    $day1Status = isset($student['checkin_1']) && $student['checkin_1'] === 'Yes' ? 
        '<span class="badge bg-success">Checked In</span>' : 
        '<span class="badge bg-danger">Not Checked In</span>';
    $day2Status = isset($student['checkin_2']) && $student['checkin_2'] === 'Yes' ? 
        '<span class="badge bg-success">Checked In</span>' : 
        '<span class="badge bg-danger">Not Checked In</span>';
    
    // Format timestamps
    function formatDateTime($dateTime) {
        if (!$dateTime) return 'Not available';
        
        $dt = new DateTime($dateTime);
        return $dt->format('F j, Y, g:i a');
    }
    
    $registrationDate = isset($student['registration_date']) ? formatDateTime($student['registration_date']) : 'Not available';
    $paymentDate = isset($student['payment_update_timestamp']) ? formatDateTime($student['payment_update_timestamp']) : 'Not available';
    
    // Get the name based on type
    $name = $type === 'alumni' ? ($student['alumni_name'] ?? 'N/A') : ($student['student_name'] ?? 'N/A');
    
    // Start output
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    
    // Basic Information
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-primary text-white"><i class="fas fa-info-circle me-2"></i>Basic Information</div>';
    echo '<div class="card-body">';
    echo '<div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">' . htmlspecialchars($name) . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">JIS ID</div><div class="detail-value">' . htmlspecialchars($student['jis_id'] ?? 'N/A') . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Department</div><div class="detail-value">' . htmlspecialchars($student['department'] ?? 'N/A') . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Gender</div><div class="detail-value">' . htmlspecialchars(ucfirst($student['gender'] ?? 'N/A')) . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Type</div><div class="detail-value">' . ucfirst($type) . '</div></div>';
    
    // Add alumni-specific fields
    if ($type === 'alumni' && isset($student['passout_year'])) {
        echo '<div class="detail-item"><div class="detail-label">Graduation Year</div><div class="detail-value">' . htmlspecialchars($student['passout_year'] ?? 'N/A') . '</div></div>';
        echo '<div class="detail-item"><div class="detail-label">Current Organization</div><div class="detail-value">' . htmlspecialchars($student['current_organization'] ?? 'N/A') . '</div></div>';
    }
    
    // Add student-specific fields
    if ($type === 'student' && isset($student['inhouse_competition'])) {
        echo '<div class="detail-item"><div class="detail-label">Inhouse Competition</div><div class="detail-value">' . htmlspecialchars($student['inhouse_competition'] ?? 'N/A') . '</div></div>';
        if (isset($student['competition_name']) && !empty($student['competition_name'])) {
            echo '<div class="detail-item"><div class="detail-label">Competition Name</div><div class="detail-value">' . htmlspecialchars($student['competition_name']) . '</div></div>';
        }
    }
    
    echo '</div></div>';
    
    // Contact Information
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-info text-white"><i class="fas fa-address-card me-2"></i>Contact Information</div>';
    echo '<div class="card-body">';
    echo '<div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">' . htmlspecialchars($student['email'] ?? 'N/A') . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">' . htmlspecialchars($student['mobile'] ?? 'N/A') . '</div></div>';
    echo '</div></div>';
    
    echo '</div>';
    echo '<div class="col-md-6">';
    
    // Status Information
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-warning text-dark"><i class="fas fa-check-circle me-2"></i>Status Information</div>';
    echo '<div class="card-body">';
    echo '<div class="detail-item"><div class="detail-label">Payment Status</div><div class="detail-value">' . $paymentStatus . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Ticket Status</div><div class="detail-value">' . $ticketStatus . '</div></div>';
    
    // Day 1 Check-in with timestamp if available
    echo '<div class="detail-item"><div class="detail-label">Day 1 Check-In</div><div class="detail-value">' . $day1Status;
    if (isset($student['checkin_1']) && $student['checkin_1'] === 'Yes' && !empty($student['checkin_1_timestamp'])) {
        echo ' <small class="text-muted">(' . formatDateTime($student['checkin_1_timestamp']) . ')</small>';
    }
    echo '</div></div>';
    
    // Day 2 Check-in with timestamp if available
    echo '<div class="detail-item"><div class="detail-label">Day 2 Check-In</div><div class="detail-value">' . $day2Status;
    if (isset($student['checkin_2']) && $student['checkin_2'] === 'Yes' && !empty($student['checkin_2_timestamp'])) {
        echo ' <small class="text-muted">(' . formatDateTime($student['checkin_2_timestamp']) . ')</small>';
    }
    echo '</div></div>';
    
    echo '</div></div>';
    
    // Payment Information
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-success text-white"><i class="fas fa-money-bill-wave me-2"></i>Payment Information</div>';
    echo '<div class="card-body">';
    echo '<div class="detail-item"><div class="detail-label">Receipt Number</div><div class="detail-value">' . htmlspecialchars($student['receipt_number'] ?? 'N/A') . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Payment Amount</div><div class="detail-value">₹' . htmlspecialchars($student['paid_amount'] ?? 'N/A') . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Payment Date</div><div class="detail-value">' . $paymentDate . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Registration Date</div><div class="detail-value">' . $registrationDate . '</div></div>';
    echo '<div class="detail-item"><div class="detail-label">Updated By</div><div class="detail-value">' . htmlspecialchars($student['payment_updated_by'] ?? 'N/A') . '</div></div>';
    echo '</div></div>';
    
    echo '</div>';
    echo '</div>';
    
    // Additional information if available
    if (!empty($student['notes']) || !empty($student['additional_info'])) {
        echo '<div class="card mb-3">';
        echo '<div class="card-header bg-secondary text-white"><i class="fas fa-sticky-note me-2"></i>Additional Information</div>';
        echo '<div class="card-body">';
        echo '<div class="detail-item">' . htmlspecialchars($student['notes'] ?? $student['additional_info'] ?? 'No additional information') . '</div>';
        echo '</div></div>';
    }

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
