<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in with Controller or Super Admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || ($_SESSION['admin_role'] !== 'Controller' && $_SESSION['admin_role'] !== 'Super Admin')) {
    // Not logged in or not a controller, redirect to login
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db_config.php';

// Get filter parameters
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$payment_status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="majistic_registrations_' . date('Y-m-d_H-i-s') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Build query conditions based on filters
$student_conditions = [];
$alumni_conditions = [];
$params = [];

if (!empty($search)) {
    $student_conditions[] = "(student_name LIKE :search OR jis_id LIKE :search OR email LIKE :search OR mobile LIKE :search OR department LIKE :search)";
    $alumni_conditions[] = "(alumni_name LIKE :search OR jis_id LIKE :search OR email LIKE :search OR mobile LIKE :search OR department LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($payment_status)) {
    $student_conditions[] = "payment_status = :payment_status";
    $alumni_conditions[] = "payment_status = :payment_status";
    $params[':payment_status'] = $payment_status;
}

if (!empty($department)) {
    $student_conditions[] = "department = :department";
    $alumni_conditions[] = "department = :department";
    $params[':department'] = $department;
}

// Build the WHERE clause for each query
$student_where = !empty($student_conditions) ? " WHERE " . implode(" AND ", $student_conditions) : "";
$alumni_where = !empty($alumni_conditions) ? " WHERE " . implode(" AND ", $alumni_conditions) : "";

// Fetch registrations based on type filter with sorting by payment_status
$registrations = [];
$alumni_registrations = [];

if ($type === 'all' || $type === 'student') {
    $query = "SELECT * FROM registrations" . $student_where . " ORDER BY CASE WHEN payment_status = 'Paid' THEN 0 ELSE 1 END";
    $stmt = $db->prepare($query);
    
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
    }
    
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($type === 'all' || $type === 'alumni') {
    $query = "SELECT * FROM alumni_registrations" . $alumni_where . " ORDER BY CASE WHEN payment_status = 'Paid' THEN 0 ELSE 1 END";
    $stmt = $db->prepare($query);
    
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
    }
    
    $stmt->execute();
    $alumni_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to convert UTC timestamp to IST
function convertToIST($utcTimestamp) {
    if (!$utcTimestamp || $utcTimestamp == '' || $utcTimestamp == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    $utcDateTime = new DateTime($utcTimestamp, new DateTimeZone('UTC'));
    $utcDateTime->setTimezone(new DateTimeZone('Asia/Kolkata'));
    return $utcDateTime->format('d M Y, h:i A');
}

// Start Excel output
echo '<table border="1">';

// Main header for maJIStic k25
echo '<tr style="background-color: #3f51b5; color: white; font-weight: bold; font-size: 16px;">';
echo '<th colspan="20">maJIStic 2k25 - Registration Report</th>';
echo '</tr>';
echo '<tr style="background-color: #e8eaf6;">';
echo '<th colspan="20">Generated on: ' . date('d M Y, h:i A', time() + 19800) . ' (IST)</th>'; // Adding 5:30 hours (19800 seconds) to current time
echo '</tr>';
echo '<tr><td colspan="20">&nbsp;</td></tr>';

// Initialize counters for students
$student_paid_count = 0;
$student_day1_checkin_count = 0;
$student_day2_checkin_count = 0;

// Only include student registrations section if there are students or if type is 'all' or 'student'
if (($type === 'all' || $type === 'student') && (!empty($registrations) || $type === 'student')) {
    // Header row for students
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<th colspan="20">STUDENT REGISTRATIONS</th>';
    echo '</tr>';

    // Create header row for student fields
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<th>S. No.</th>';  // Changed from ID to S. No.
    echo '<th>Name</th>';
    echo '<th>Gender</th>';
    echo '<th>JIS ID</th>';
    echo '<th>Mobile</th>';
    echo '<th>Email</th>';
    echo '<th>Department</th>';
    echo '<th>Event</th>';  // Changed from "Competition" to "Event"
    echo '<th>Event Name</th>';  // Changed from "Competition Name" to "Event Name"
    echo '<th>Registration Date</th>';
    echo '<th>Payment Status</th>';
    echo '<th>Receipt Number</th>';
    echo '<th>Payment Updated By</th>';
    echo '<th>Payment Date</th>';
    echo '<th>Paid Amount</th>';
    echo '<th>Ticket Generated</th>';
    echo '<th>Day 1 Check-in</th>';
    echo '<th>Day 1 Check-in Time</th>';
    echo '<th>Day 2 Check-in</th>';
    echo '<th>Day 2 Check-in Time</th>';
    echo '</tr>';

    // Initialize serial number counter for students
    $studentSerialNo = 1;
    
    // Output each student registration as a row
    foreach ($registrations as $student) {
        echo '<tr>';
        echo '<td>' . $studentSerialNo++ . '</td>';  // Display serial number instead of ID
        echo '<td>' . htmlspecialchars($student['student_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['gender'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['jis_id'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['mobile'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['email'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['department'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['inhouse_competition'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['competition_name'] ?? '') . '</td>';
        echo '<td>' . convertToIST($student['registration_date']) . '</td>';
        echo '<td>' . htmlspecialchars($student['payment_status'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['receipt_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['payment_updated_by'] ?? '') . '</td>';
        echo '<td>' . convertToIST($student['payment_update_timestamp']) . '</td>';
        echo '<td>' . htmlspecialchars($student['paid_amount'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['ticket_generated'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($student['checkin_1'] ?? '') . '</td>';
        echo '<td>' . convertToIST($student['checkin_1_timestamp']) . '</td>';
        echo '<td>' . htmlspecialchars($student['checkin_2'] ?? '') . '</td>';
        echo '<td>' . convertToIST($student['checkin_2_timestamp']) . '</td>';
        echo '</tr>';
        
        // Count paid registrations only
        if ($student['payment_status'] === 'Paid') {
            $student_paid_count++;
        }
        
        // Count day 1 check-ins
        if ($student['checkin_1'] === 'Yes') {
            $student_day1_checkin_count++;
        }
        
        // Count day 2 check-ins
        if ($student['checkin_2'] === 'Yes') {
            $student_day2_checkin_count++;
        }
    }

    // If no student records found, show a message
    if (empty($registrations) && $type === 'student') {
        echo '<tr><td colspan="20" style="text-align: center;">No student registrations found matching your criteria.</td></tr>';
    } else {
        // Add student summary row
        echo '<tr style="background-color: #c8e6c9; font-weight: bold;">';
        echo '<td colspan="10" style="text-align: right;">STUDENT TOTALS:</td>';
        echo '<td>' . $student_paid_count . ' Paid</td>';
        echo '<td colspan="5"></td>';
        echo '<td>' . $student_day1_checkin_count . ' Checked In</td>';
        echo '<td></td>';
        echo '<td>' . $student_day2_checkin_count . ' Checked In</td>';
        echo '<td></td>';
        echo '</tr>';
    }

    // Add empty row as separator if showing both types
    if ($type === 'all' && !empty($alumni_registrations)) {
        echo '<tr><td colspan="20">&nbsp;</td></tr>';
    }
}

// Initialize counters for alumni
$alumni_paid_count = 0;
$alumni_day1_checkin_count = 0;
$alumni_day2_checkin_count = 0;

// Only include alumni registrations section if there are alumni or if type is 'all' or 'alumni'
if (($type === 'all' || $type === 'alumni') && (!empty($alumni_registrations) || $type === 'alumni')) {
    // Header row for alumni
    echo '<tr style="background-color: #FF9800; color: white; font-weight: bold;">';
    echo '<th colspan="20">ALUMNI REGISTRATIONS</th>';
    echo '</tr>';

    // Create header row for alumni fields
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<th>S. No.</th>';  // Changed from ID to S. No.
    echo '<th>Name</th>';
    echo '<th>Gender</th>';
    echo '<th>JIS ID</th>';
    echo '<th>Mobile</th>';
    echo '<th>Email</th>';
    echo '<th>Department</th>';
    echo '<th>Passout Year</th>';
    echo '<th>Current Organization</th>';
    echo '<th>Registration Date</th>';
    echo '<th>Payment Status</th>';
    echo '<th>Receipt Number</th>';
    echo '<th>Payment Updated By</th>';
    echo '<th>Payment Date</th>';
    echo '<th>Paid Amount</th>';
    echo '<th>Ticket Generated</th>';
    echo '<th>Day 1 Check-in</th>';
    echo '<th>Day 1 Check-in Time</th>';
    echo '<th>Day 2 Check-in</th>';
    echo '<th>Day 2 Check-in Time</th>';
    echo '</tr>';

    // Initialize serial number counter for alumni
    $alumniSerialNo = 1;
    
    // Output each alumni registration as a row
    foreach ($alumni_registrations as $alumni) {
        echo '<tr>';
        echo '<td>' . $alumniSerialNo++ . '</td>';  // Display serial number instead of ID
        echo '<td>' . htmlspecialchars($alumni['alumni_name'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['gender'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['jis_id'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['mobile'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['email'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['department'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['passout_year'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['current_organization'] ?? '') . '</td>';
        echo '<td>' . convertToIST($alumni['registration_date']) . '</td>';
        echo '<td>' . htmlspecialchars($alumni['payment_status'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['receipt_number'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['payment_updated_by'] ?? '') . '</td>';
        echo '<td>' . convertToIST($alumni['payment_update_timestamp']) . '</td>';
        echo '<td>' . htmlspecialchars($alumni['paid_amount'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['ticket_generated'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($alumni['checkin_1'] ?? '') . '</td>';
        echo '<td>' . convertToIST($alumni['checkin_1_timestamp']) . '</td>';
        echo '<td>' . htmlspecialchars($alumni['checkin_2'] ?? '') . '</td>';
        echo '<td>' . convertToIST($alumni['checkin_2_timestamp']) . '</td>';
        echo '</tr>';
        
        // Count paid registrations only
        if ($alumni['payment_status'] === 'Paid') {
            $alumni_paid_count++;
        }
        
        // Count day 1 check-ins
        if ($alumni['checkin_1'] === 'Yes') {
            $alumni_day1_checkin_count++;
        }
        
        // Count day 2 check-ins
        if ($alumni['checkin_2'] === 'Yes') {
            $alumni_day2_checkin_count++;
        }
    }
    
    // If no alumni records found, show a message
    if (empty($alumni_registrations) && $type === 'alumni') {
        echo '<tr><td colspan="20" style="text-align: center;">No alumni registrations found matching your criteria.</td></tr>';
    } else {
        // Add alumni summary row
        echo '<tr style="background-color: #ffe0b2; font-weight: bold;">';
        echo '<td colspan="10" style="text-align: right;">ALUMNI TOTALS:</td>';
        echo '<td>' . $alumni_paid_count . ' Paid</td>';
        echo '<td colspan="5"></td>';
        echo '<td>' . $alumni_day1_checkin_count . ' Checked In</td>';
        echo '<td></td>';
        echo '<td>' . $alumni_day2_checkin_count . ' Checked In</td>';
        echo '<td></td>';
        echo '</tr>';
    }
}

// Add grand total row if showing both types
if ($type === 'all' && (!empty($registrations) || !empty($alumni_registrations))) {
    $total_paid = $student_paid_count + $alumni_paid_count;
    $total_day1 = $student_day1_checkin_count + $alumni_day1_checkin_count;
    $total_day2 = $student_day2_checkin_count + $alumni_day2_checkin_count;

    echo '<tr><td colspan="20">&nbsp;</td></tr>';
    echo '<tr style="background-color: #bbdefb; font-weight: bold;">';
    echo '<td colspan="10" style="text-align: right;">GRAND TOTALS:</td>';
    echo '<td>' . $total_paid . ' Paid</td>';
    echo '<td colspan="5"></td>';
    echo '<td>' . $total_day1 . ' Checked In</td>';
    echo '<td></td>';
    echo '<td>' . $total_day2 . ' Checked In</td>';
    echo '<td></td>';
    echo '</tr>';
}

// Add report footer
echo '<tr style="background-color: #f5f5f5;">';
echo '<th colspan="20">End of Report - maJIStic 2k25</th>';
echo '</tr>';

// End Excel output
echo '</table>';
exit;
?>
