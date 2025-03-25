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
    
    // Build HTML form content for modal
    $html = '<form id="editRegistrationForm" method="post" action="update_registration.php">';
    $html .= '<input type="hidden" name="id" value="' . $id . '">';
    $html .= '<input type="hidden" name="type" value="' . $type . '">';
    
    // Basic Information
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6">';
    $html .= '<label for="jis_id" class="form-label">JIS ID</label>';
    $html .= '<input type="text" class="form-control" id="jis_id" name="jis_id" value="' . htmlspecialchars($registration['jis_id']) . '" required>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<label for="name" class="form-label">Name</label>';
    $html .= '<input type="text" class="form-control" id="name" name="name" value="' . htmlspecialchars($registration[$name_field]) . '" required>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Second Row
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6">';
    $html .= '<label for="department" class="form-label">Department</label>';
    $html .= '<select class="form-select" id="department" name="department" required>';
    $html .= '<option value="">--Select Department--</option>';
    
    $departments = [
        'CSE', 'CSE AI-ML', 'CST', 'IT', 'ECE', 'EE', 'BME', 'CE', 'ME', 'AGE', 
        'BBA', 'MBA', 'BCA', 'MCA', 'Diploma ME', 'Diploma CE', 'Diploma EE', 'B. Pharmacy'
    ];
    
    foreach ($departments as $dept) {
        $selected = ($registration['department'] === $dept) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($dept) . '" ' . $selected . '>' . htmlspecialchars($dept) . '</option>';
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    if ($type === 'student') {
        $html .= '<label for="gender" class="form-label">Gender</label>';
        $html .= '<select class="form-select" id="gender" name="gender" required>';
        $html .= '<option value="">--Select Gender--</option>';
        $html .= '<option value="Male" ' . ($registration['gender'] === 'Male' ? 'selected' : '') . '>Male</option>';
        $html .= '<option value="Female" ' . ($registration['gender'] === 'Female' ? 'selected' : '') . '>Female</option>';
        $html .= '<option value="Other" ' . ($registration['gender'] === 'Other' ? 'selected' : '') . '>Other</option>';
        $html .= '</select>';
    } else {
        $html .= '<label for="passout_year" class="form-label">Passout Year</label>';
        $html .= '<select class="form-select" id="passout_year" name="passout_year" required>';
        $html .= '<option value="">--Select Year--</option>';
        
        $current_year = date('Y');
        for ($year = $current_year; $year >= 1990; $year--) {
            $selected = ($registration['passout_year'] == $year) ? 'selected' : '';
            $html .= '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
        }
        
        $html .= '</select>';
    }
    $html .= '</div>';
    $html .= '</div>';
    
    // Third Row
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6">';
    $html .= '<label for="email" class="form-label">Email</label>';
    $html .= '<input type="email" class="form-control" id="email" name="email" value="' . htmlspecialchars($registration['email']) . '" required>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<label for="mobile" class="form-label">Mobile</label>';
    $html .= '<input type="text" class="form-control" id="mobile" name="mobile" value="' . htmlspecialchars($registration['mobile']) . '" required>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Type-specific fields
    if ($type === 'student') {
        $html .= '<div class="row mb-3">';
        $html .= '<div class="col-md-6">';
        $html .= '<label class="form-label">Participating in In-house Competition?</label>';
        $html .= '<div class="form-check">';
        $html .= '<input class="form-check-input" type="radio" name="inhouse_competition" id="inhouse_yes" value="Yes" ' . (($registration['inhouse_competition'] ?? '') === 'Yes' ? 'checked' : '') . '>';
        $html .= '<label class="form-check-label" for="inhouse_yes">Yes</label>';
        $html .= '</div>';
        $html .= '<div class="form-check">';
        $html .= '<input class="form-check-input" type="radio" name="inhouse_competition" id="inhouse_no" value="No" ' . (($registration['inhouse_competition'] ?? '') !== 'Yes' ? 'checked' : '') . '>';
        $html .= '<label class="form-check-label" for="inhouse_no">No</label>';
        $html .= '</div>';
        $html .= '</div>';
        
        $display = ($registration['inhouse_competition'] ?? '') === 'Yes' ? 'block' : 'none';
        $html .= '<div class="col-md-6" id="competition_group" style="display: ' . $display . ';">';
        $html .= '<label for="competition_name" class="form-label">Competition</label>';
        $html .= '<select class="form-select" id="competition_name" name="competition_name">';
        $html .= '<option value="">--Select Competition--</option>';
        
        $competitions = [
            'Taal Se Taal Mila (Dance)', 
            'Actomania (Drama)', 
            'Jam Room (Band)', 
            'Fashion Fiesta (Fashion Show)'
        ];
        
        foreach ($competitions as $comp) {
            $selected = (($registration['competition_name'] ?? '') === $comp) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($comp) . '" ' . $selected . '>' . htmlspecialchars($comp) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
    } elseif ($type === 'alumni') {
        $html .= '<div class="row mb-3">';
        $html .= '<div class="col-md-12">';
        $html .= '<label for="current_organization" class="form-label">Current Organization</label>';
        $html .= '<input type="text" class="form-control" id="current_organization" name="current_organization" value="' . htmlspecialchars($registration['current_organization'] ?? '') . '">';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // Payment status (read-only)
    $html .= '<div class="row mb-3">';
    $html .= '<div class="col-md-6">';
    $html .= '<label for="payment_status" class="form-label">Payment Status</label>';
    $html .= '<div class="form-control bg-light">';
    $html .= '<span class="badge bg-' . ($registration['payment_status'] === 'Paid' ? 'success' : 'warning') . ' p-2">';
    $html .= $registration['payment_status'];
    $html .= '</span>';
    $html .= '</div>';
    $html .= '<small class="text-muted">* Payment status can only be updated from the main page</small>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Submit buttons
    $html .= '<div class="text-center mt-4">';
    $html .= '<button type="submit" class="btn btn-primary mx-2">Save Changes</button>';
    $html .= '<button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">Cancel</button>';
    $html .= '</div>';
    
    $html .= '</form>';
    
    echo json_encode(['status' => 'success', 'html' => $html]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
