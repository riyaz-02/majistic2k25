<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Handle AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if (empty($type) || $id <= 0) {
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid registration specified']);
        } else {
            $_SESSION['error_message'] = "Invalid registration specified.";
            header('Location: index.php?page=all_registrations');
        }
        exit;
    }
    
    // Determine the table based on registration type
    $table = $type === 'student' ? 'registrations' : 'alumni_registrations';
    $name_field = $type === 'student' ? 'student_name' : 'alumni_name';
    
    try {
        // Prepare base data array with common fields
        $data = [
            'jis_id' => $_POST['jis_id'],
            'email' => $_POST['email'],
            'mobile' => $_POST['mobile'],
            'department' => $_POST['department'],
            'id' => $id
        ];
        
        // Add type-specific fields
        if ($type === 'student') {
            $data['student_name'] = $_POST['name'];
            $data['gender'] = $_POST['gender'];
            
            // Handle competition fields
            if (isset($_POST['inhouse_competition']) && $_POST['inhouse_competition'] === 'Yes') {
                $data['inhouse_competition'] = 'Yes';
                $data['competition_name'] = $_POST['competition_name'] ?? '';
            } else {
                $data['inhouse_competition'] = 'No';
                $data['competition_name'] = '';
            }
            
            // Build query
            $query = "UPDATE registrations SET 
                student_name = :student_name,
                jis_id = :jis_id, 
                gender = :gender,
                email = :email, 
                mobile = :mobile, 
                department = :department,
                inhouse_competition = :inhouse_competition,
                competition_name = :competition_name
                WHERE id = :id";
        } else {
            $data['alumni_name'] = $_POST['name'];
            $data['passout_year'] = $_POST['passout_year'];
            $data['current_organization'] = $_POST['current_organization'] ?? '';
            
            // Build query
            $query = "UPDATE alumni_registrations SET 
                alumni_name = :alumni_name,
                jis_id = :jis_id, 
                email = :email, 
                mobile = :mobile, 
                department = :department,
                passout_year = :passout_year,
                current_organization = :current_organization
                WHERE id = :id";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        
        if ($is_ajax) {
            echo json_encode(['status' => 'success', 'message' => 'Registration updated successfully']);
        } else {
            $_SESSION['success_message'] = "Registration updated successfully!";
            header("Location: index.php?page=all_registrations");
        }
        exit;
    } catch (PDOException $e) {
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        } else {
            $_SESSION['error_message'] = "Error updating registration: " . $e->getMessage();
            header("Location: index.php?page=all_registrations");
        }
        exit;
    }
} else {
    // If not POST request, redirect to registrations page
    header('Location: index.php?page=all_registrations');
    exit;
}
