<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../includes/db_config.php';

// Fetch filtered data if filters are applied
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$competition_filter = isset($_GET['competition']) ? $_GET['competition'] : '';
$payment_status_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$college_filter = isset($_GET['college']) ? $_GET['college'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'inhouse';

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10; // Number of records per page
$offset = ($page - 1) * $items_per_page;

// Prepare inhouse query with filters
$inhouse_query = "SELECT * FROM registrations WHERE 1=1";
if ($gender_filter) {
    $inhouse_query .= " AND gender = '$gender_filter'";
}
if ($competition_filter) {
    $inhouse_query .= " AND competition_name = '$competition_filter'";
}
if ($payment_status_filter) {
    $inhouse_query .= " AND payment_status = '$payment_status_filter'";
}
if ($department_filter) {
    $inhouse_query .= " AND department = '$department_filter'";
}

// Count total inhouse records for pagination
$inhouse_count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $inhouse_query);
$inhouse_count_result = $conn->query($inhouse_count_query);
$inhouse_total_records = $inhouse_count_result->fetch_assoc()['total'];
$inhouse_total_pages = ceil($inhouse_total_records / $items_per_page);

// Add pagination limits to inhouse query
$inhouse_data_query = $inhouse_query . " ORDER BY registration_date DESC LIMIT $offset, $items_per_page";
$inhouse_result = $conn->query($inhouse_data_query);

// Prepare outhouse query with filters
$outhouse_query = "SELECT * FROM registrations_outhouse WHERE 1=1";
if ($gender_filter) {
    $outhouse_query .= " AND gender = '$gender_filter'";
}
if ($competition_filter) {
    $outhouse_query .= " AND competition_name = '$competition_filter'";
}
if ($payment_status_filter) {
    $outhouse_query .= " AND payment_status = '$payment_status_filter'";
}
if ($college_filter) {
    $outhouse_query .= " AND college_name = '$college_filter'";
}

// Count total outhouse records for pagination
$outhouse_count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $outhouse_query);
$outhouse_count_result = $conn->query($outhouse_count_query);
$outhouse_total_records = $outhouse_count_result->fetch_assoc()['total'];
$outhouse_total_pages = ceil($outhouse_total_records / $items_per_page);

// Add pagination limits to outhouse query
$outhouse_data_query = $outhouse_query . " ORDER BY registration_date DESC LIMIT $offset, $items_per_page";
$outhouse_result = $conn->query($outhouse_data_query);

// Fetch statistics
$inhouse_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Paid'";
$inhouse_not_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Not Paid'";
$outhouse_paid_query = "SELECT COUNT(*) as count FROM registrations_outhouse WHERE payment_status = 'Paid'";
$outhouse_not_paid_query = "SELECT COUNT(*) as count FROM registrations_outhouse WHERE payment_status = 'Not Paid'";
$inhouse_total_query = "SELECT COUNT(*) as count FROM registrations";
$outhouse_total_query = "SELECT COUNT(*) as count FROM registrations_outhouse";

$inhouse_paid_result = $conn->query($inhouse_paid_query)->fetch_assoc();
$inhouse_not_paid_result = $conn->query($inhouse_not_paid_query)->fetch_assoc();
$outhouse_paid_result = $conn->query($outhouse_paid_query)->fetch_assoc();
$outhouse_not_paid_result = $conn->query($outhouse_not_paid_query)->fetch_assoc();
$inhouse_total_result = $conn->query($inhouse_total_query)->fetch_assoc();
$outhouse_total_result = $conn->query($outhouse_total_query)->fetch_assoc();

// Get all competitions for dropdowns
$inhouse_competitions_query = "SELECT DISTINCT competition_name FROM registrations ORDER BY competition_name";
$outhouse_competitions_query = "SELECT DISTINCT competition_name FROM registrations_outhouse ORDER BY competition_name";

$inhouse_competitions = $conn->query($inhouse_competitions_query);
$outhouse_competitions = $conn->query($outhouse_competitions_query);

// Get all departments
$departments_query = "SELECT DISTINCT department FROM registrations WHERE department != '' ORDER BY department";
$departments = $conn->query($departments_query);

// Get all colleges
$colleges_query = "SELECT DISTINCT college_name FROM registrations_outhouse WHERE college_name != '' ORDER BY college_name";
$colleges = $conn->query($colleges_query);

// Calculate revenue statistics
$inhouse_revenue_query = "SELECT SUM(amount_paid) as total FROM registrations WHERE payment_status = 'Paid'";
$outhouse_revenue_query = "SELECT SUM(amount_paid) as total FROM registrations_outhouse WHERE payment_status = 'Paid'";
$inhouse_revenue = $conn->query($inhouse_revenue_query)->fetch_assoc()['total'] ?: 0;
$outhouse_revenue = $conn->query($outhouse_revenue_query)->fetch_assoc()['total'] ?: 0;
$total_revenue = $inhouse_revenue + $outhouse_revenue;

// Function to handle CSV exports
function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    $f = fopen('php://output', 'w');
    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
    fclose($f);
}

// Handle CSV download requests
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    $data = [];
    $export_date = date('Y-m-d');
    
    if ($tab == 'inhouse') {
        // Remove pagination for export
        $inhouse_export_query = str_replace(" LIMIT $offset, $items_per_page", "", $inhouse_data_query);
        $inhouse_export_result = $conn->query($inhouse_export_query);
        
        $data[] = ['Sl. No.', 'Student Name', 'JIS ID', 'Mobile', 'Email', 'Roll No', 'Gender', 'Department', 'Inhouse Competition', 'Competition', 'Payment Status', 'Payment ID', 'Amount', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $inhouse_export_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['student_name'],
                $row['jis_id'],
                $row['mobile'],
                $row['email'],
                $row['roll_no'],
                $row['gender'],
                $row['department'],
                $row['inhouse_competition'],
                $row['competition_name'],
                $row['payment_status'],
                $row['payment_id'],
                $row['amount'],
                $row['amount_paid'],
                $row['payment_date'],
                $row['registration_date']
            ];
        }
        
        array_to_csv_download($data, "inhouse_registrations_{$export_date}.csv");
    } else {
        // Remove pagination for export
        $outhouse_export_query = str_replace(" LIMIT $offset, $items_per_page", "", $outhouse_data_query);
        $outhouse_export_result = $conn->query($outhouse_export_query);
        
        $data[] = ['Sl. No.', 'Leader Name', 'Gender', 'Email', 'Contact Number', 'College Name', 'College ID', 'Course Name', 'Competition Name', 'Team Name', 'Team Members', 'Team Members Contact', 'Payment Status', 'Payment ID', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $outhouse_export_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['leader_name'],
                $row['gender'],
                $row['email'],
                $row['contact_number'],
                $row['college_name'],
                $row['college_id'],
                $row['course_name'],
                $row['competition_name'],
                $row['team_name'],
                isset($row['team_members']) ? implode(", ", json_decode($row['team_members'], true) ?: []) : '',
                isset($row['team_members_contact']) ? implode(", ", json_decode($row['team_members_contact'], true) ?: []) : '',
                $row['payment_status'],
                $row['payment_id'],
                $row['amount_paid'],
                $row['payment_date'],
                $row['registration_date']
            ];
        }
        
        array_to_csv_download($data, "outhouse_registrations_{$export_date}.csv");
    }
    exit();
}
?>
