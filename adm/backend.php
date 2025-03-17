<?php
// Only start session if one doesn't exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$passout_year_filter = isset($_GET['passout_year']) ? $_GET['passout_year'] : '';
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

// Prepare alumni query with filters
$alumni_query = "SELECT * FROM alumni_registrations WHERE 1=1";
if ($gender_filter) {
    $alumni_query .= " AND gender = '$gender_filter'";
}
if ($payment_status_filter) {
    $alumni_query .= " AND payment_status = '$payment_status_filter'";
}
if ($passout_year_filter) {
    $alumni_query .= " AND passout_year = '$passout_year_filter'";
}
if ($department_filter) {
    $alumni_query .= " AND department = '$department_filter'";
}

// Count total alumni records for pagination
$alumni_count_query = str_replace("SELECT *", "SELECT COUNT(*) as total", $alumni_query);
$alumni_count_result = $conn->query($alumni_count_query);
$alumni_total_records = $alumni_count_result->fetch_assoc()['total'];
$alumni_total_pages = ceil($alumni_total_records / $items_per_page);

// Add pagination limits to alumni query
$alumni_data_query = $alumni_query . " ORDER BY registration_date DESC LIMIT $offset, $items_per_page";
$alumni_result = $conn->query($alumni_data_query);

// Fetch statistics
$inhouse_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Paid'";
$inhouse_not_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Not Paid'";
$alumni_paid_query = "SELECT COUNT(*) as count FROM alumni_registrations WHERE payment_status = 'Paid'";
$alumni_not_paid_query = "SELECT COUNT(*) as count FROM alumni_registrations WHERE payment_status = 'Not Paid'";
$inhouse_total_query = "SELECT COUNT(*) as count FROM registrations";
$alumni_total_query = "SELECT COUNT(*) as count FROM alumni_registrations";

$inhouse_paid_result = $conn->query($inhouse_paid_query)->fetch_assoc();
$inhouse_not_paid_result = $conn->query($inhouse_not_paid_query)->fetch_assoc();
$alumni_paid_result = $conn->query($alumni_paid_query)->fetch_assoc();
$alumni_not_paid_result = $conn->query($alumni_not_paid_query)->fetch_assoc();
$inhouse_total_result = $conn->query($inhouse_total_query)->fetch_assoc();
$alumni_total_result = $conn->query($alumni_total_query)->fetch_assoc();

// Get all competitions for dropdowns
$inhouse_competitions_query = "SELECT DISTINCT competition_name FROM registrations ORDER BY competition_name";
$inhouse_competitions = $conn->query($inhouse_competitions_query);

// Get all departments
$departments_query = "SELECT DISTINCT department FROM registrations WHERE department != '' ORDER BY department";
$departments = $conn->query($departments_query);

// Get all passout years
$passout_years_query = "SELECT DISTINCT passout_year FROM alumni_registrations WHERE passout_year != '' ORDER BY passout_year DESC";
$passout_years = $conn->query($passout_years_query);

// Calculate revenue statistics
$inhouse_revenue_query = "SELECT SUM(amount_paid) as total FROM registrations WHERE payment_status = 'Paid'";
$alumni_revenue_query = "SELECT SUM(amount_paid) as total FROM alumni_registrations WHERE payment_status = 'Paid'";
$inhouse_revenue = $conn->query($inhouse_revenue_query)->fetch_assoc()['total'] ?: 0;
$alumni_revenue = $conn->query($alumni_revenue_query)->fetch_assoc()['total'] ?: 0;
$total_revenue = $inhouse_revenue + $alumni_revenue;

// Function to handle CSV exports
function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    $f = fopen('php://output', 'w');
    foreach ($array as $line) {
        fputcsv($f, $delimiter);
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
        
        $data[] = ['Sl. No.', 'Student Name', 'JIS ID', 'Mobile', 'Email', 'Gender', 'Department', 'Inhouse Competition', 'Competition', 'Payment Status', 'Payment ID', 'Amount', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $inhouse_export_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['student_name'],
                $row['jis_id'],
                $row['mobile'],
                $row['email'],
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
    } else if ($tab == 'alumni') {
        // Remove pagination for export
        $alumni_export_query = str_replace(" LIMIT $offset, $items_per_page", "", $alumni_data_query);
        $alumni_export_result = $conn->query($alumni_export_query);
        
        $data[] = ['Sl. No.', 'Name', 'JIS ID', 'Gender', 'Email', 'Mobile', 'Passout Year', 'Department', 'Current Organization', 'Payment Status', 'Payment ID', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $alumni_export_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['alumni_name'],
                $row['jis_id'],
                $row['gender'],
                $row['email'],
                $row['mobile'],
                $row['passout_year'],
                $row['department'],
                $row['current_organization'],
                $row['payment_status'],
                $row['payment_id'],
                $row['amount_paid'],
                $row['payment_date'],
                $row['registration_date']
            ];
        }
        
        array_to_csv_download($data, "alumni_registrations_{$export_date}.csv");
    }
    exit();
}
?>
