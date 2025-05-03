<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in with appropriate role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['Controller', 'Super Admin', 'Convenor', 'Department Coordinator'])) {
    // Not logged in or not authorized
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

require_once __DIR__ . '/../../includes/db_config.php';

// Function to format timestamp to IST
function formatToIST($timestamp) {
    if (!$timestamp) return 'Not checked in';
    
    try {
        // Convert to DateTime object
        $dt = new DateTime($timestamp);
        
        // Add 5 hours and 30 minutes for IST
        $dt->modify('+5 hours 30 minutes');
        
        // Format timestamp with IST indicator
        return $dt->format('d-M-Y h:i A') . ' IST';
    } catch (Exception $e) {
        error_log("Error formatting timestamp: " . $e->getMessage());
        return 'Invalid timestamp';
    }
}

// Get student ID and type from request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

if ($id <= 0 || empty($type)) {
    echo '<div class="alert alert-danger">Invalid request parameters</div>';
    exit;
}

try {
    // Determine which table to query based on type
    $table = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';
    
    // Fetch student details
    $query = "SELECT * FROM $table WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo '<div class="alert alert-danger">Student record not found</div>';
        exit;
    }
    
    // For Department Coordinator, check if the student belongs to their department
    if ($_SESSION['admin_role'] === 'Department Coordinator' && isset($_SESSION['admin_department'])) {
        // Always allow Department Coordinators to view student details
        // No department restriction needed as they're cleared to view all records
    }
    
    // Display student details
    $name = $type === 'alumni' ? ($student['alumni_name'] ?? 'N/A') : ($student['student_name'] ?? 'N/A');
    $jisId = $student['jis_id'] ?? 'N/A';
    $department = $student['department'] ?? 'N/A';
    $phone = $student['mobile'] ?? 'N/A';
    $email = $student['email'] ?? 'N/A';
    $paymentStatus = $student['payment_status'] ?? 'Not Paid';
    $registrationDate = isset($student['registration_date']) ? 
        formatToIST($student['registration_date']) : 'N/A';
    
    // Alumni specific fields
    $passoutYear = isset($student['passout_year']) ? $student['passout_year'] : null;
    $organization = isset($student['current_organization']) ? $student['current_organization'] : null;
    
    // Check-in information
    $day1Status = isset($student['checkin_1']) && $student['checkin_1'] === "Yes" ? "Yes" : "No";
    $day2Status = isset($student['checkin_2']) && $student['checkin_2'] === "Yes" ? "Yes" : "No";
    
    $day1Timestamp = isset($student['checkin_1_timestamp']) && !empty($student['checkin_1_timestamp']) ? 
        formatToIST($student['checkin_1_timestamp']) : 'N/A';
    
    $day2Timestamp = isset($student['checkin_2_timestamp']) && !empty($student['checkin_2_timestamp']) ? 
        formatToIST($student['checkin_2_timestamp']) : 'N/A';
    
    // Payment details
    $paidAmount = isset($student['paid_amount']) ? $student['paid_amount'] : null;
    $receiptNumber = isset($student['receipt_number']) ? $student['receipt_number'] : null;
    $paymentUpdatedBy = isset($student['payment_updated_by']) ? $student['payment_updated_by'] : null;
    $paymentTimestamp = isset($student['payment_update_timestamp']) && !empty($student['payment_update_timestamp']) ? 
        formatToIST($student['payment_update_timestamp']) : 'N/A';
    
    // Ticket generation status
    $ticketGenerated = isset($student['ticket_generated']) && $student['ticket_generated'] === "Yes" ? "Yes" : "No";
    
    // Build HTML response
    ?>
    <div class="student-details">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h5 class="mb-1"><i class="fas fa-info-circle me-2"></i>Student Information</h5>
                    <p class="mb-0">Displaying detailed information for <?php echo htmlspecialchars($name); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td><?php echo htmlspecialchars($name); ?></td>
                            </tr>
                            <tr>
                                <th>JIS ID:</th>
                                <td><?php echo htmlspecialchars($jisId); ?></td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td><?php echo htmlspecialchars($department); ?></td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td><?php echo htmlspecialchars($phone); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($email); ?></td>
                            </tr>
                            <?php if ($type === 'alumni'): ?>
                            <tr>
                                <th>Passout Year:</th>
                                <td><?php echo htmlspecialchars($passoutYear ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Current Organization:</th>
                                <td><?php echo htmlspecialchars($organization ?? 'N/A'); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Registration Date:</th>
                                <td><?php echo htmlspecialchars($registrationDate); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment & Check-in Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Payment Status:</th>
                                <td>
                                    <span class="badge bg-<?php echo ($paymentStatus === 'Paid') ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($paymentStatus); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if ($paymentStatus === 'Paid'): ?>
                            <tr>
                                <th>Amount Paid:</th>
                                <td>₹<?php echo htmlspecialchars($paidAmount ?? '0'); ?></td>
                            </tr>
                            <tr>
                                <th>Receipt Number:</th>
                                <td><?php echo htmlspecialchars($receiptNumber ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Updated By:</th>
                                <td><?php echo htmlspecialchars($paymentUpdatedBy ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Payment Date:</th>
                                <td><?php echo htmlspecialchars($paymentTimestamp); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Ticket Generated:</th>
                                <td>
                                    <span class="badge bg-<?php echo ($ticketGenerated === 'Yes') ? 'info' : 'warning'; ?>">
                                        <?php echo htmlspecialchars($ticketGenerated); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Day 1 Check-in:</th>
                                <td>
                                    <span class="badge bg-<?php echo ($day1Status === 'Yes') ? 'success' : 'secondary'; ?>">
                                        <?php echo $day1Status; ?>
                                    </span>
                                    <?php echo ($day1Status === 'Yes') ? " at $day1Timestamp" : ''; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Day 2 Check-in:</th>
                                <td>
                                    <span class="badge bg-<?php echo ($day2Status === 'Yes') ? 'success' : 'secondary'; ?>">
                                        <?php echo $day2Status; ?>
                                    </span>
                                    <?php echo ($day2Status === 'Yes') ? " at $day2Timestamp" : ''; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
