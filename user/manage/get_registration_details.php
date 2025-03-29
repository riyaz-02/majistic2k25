<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Default response
$response = [
    'success' => false, 
    'message' => 'Invalid request',
    'html' => '',
    'email' => '',
    'name' => '',
    'payment_status' => ''
];

// Validate inputs
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$id = $_GET['id'];
$type = $_GET['type'];

try {
    if ($type === 'student') {
        $query = "SELECT * FROM registrations WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            // Format the HTML display
            $html = '<div class="row">
                        <div class="col-md-6">
                            <p><strong>JIS ID:</strong> ' . htmlspecialchars($data['jis_id']) . '</p>
                            <p><strong>Name:</strong> ' . htmlspecialchars($data['student_name']) . '</p>
                            <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                            <p><strong>Mobile:</strong> ' . htmlspecialchars($data['mobile']) . '</p>
                            <p><strong>Department:</strong> ' . htmlspecialchars($data['department']) . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Registration Date:</strong> ' . htmlspecialchars($data['registration_date']) . '</p>';
            
            if ($data['payment_status'] === 'Paid') {
                $html .= '<p><strong>Payment Date:</strong> ' . htmlspecialchars($data['payment_date'] ?? 'N/A') . '</p>';
            }
            
            $html .= '  </div>
                     </div>';
            
            if (!empty($data['competition'])) {
                $html .= '<div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Competition:</strong> ' . htmlspecialchars($data['competition']) . '</p>
                            </div>
                         </div>';
            }
            
            $response = [
                'success' => true,
                'html' => $html,
                'email' => $data['email'],
                'name' => $data['student_name'],
                'payment_status' => $data['payment_status']
            ];
        } else {
            $response = ['success' => false, 'message' => 'Student registration not found'];
        }
    } elseif ($type === 'alumni') {
        $query = "SELECT * FROM alumni_registrations WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            // Format the HTML display
            $html = '<div class="row">
                        <div class="col-md-6">
                            <p><strong>JIS ID:</strong> ' . htmlspecialchars($data['jis_id']) . '</p>
                            <p><strong>Name:</strong> ' . htmlspecialchars($data['alumni_name']) . '</p>
                            <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                            <p><strong>Mobile:</strong> ' . htmlspecialchars($data['mobile']) . '</p>
                            <p><strong>Department:</strong> ' . htmlspecialchars($data['department']) . '</p>
                            <p><strong>Passout Year:</strong> ' . htmlspecialchars($data['passout_year']) . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Registration Date:</strong> ' . htmlspecialchars($data['registration_date']) . '</p>';
            
            if ($data['payment_status'] === 'Paid') {
                $html .= '<p><strong>Payment Date:</strong> ' . htmlspecialchars($data['payment_date'] ?? 'N/A') . '</p>';
            }
            
            $html .= '  </div>
                     </div>';
            
            if (!empty($data['company'])) {
                $html .= '<div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Company:</strong> ' . htmlspecialchars($data['company']) . '</p>
                            </div>
                         </div>';
            }
            
            $response = [
                'success' => true,
                'html' => $html,
                'email' => $data['email'],
                'name' => $data['alumni_name'],
                'payment_status' => $data['payment_status']
            ];
        } else {
            $response = ['success' => false, 'message' => 'Alumni registration not found'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Invalid registration type'];
    }
} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
